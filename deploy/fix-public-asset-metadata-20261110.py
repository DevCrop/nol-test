from pathlib import Path,PurePosixPath
from io import BytesIO
from pypdf import PdfReader,PdfWriter
from pypdf.generic import NameObject
import zipfile,unicodedata,xml.etree.ElementTree as ET,json,hashlib,subprocess
ROOT=Path(__file__).resolve().parents[1];qa=ROOT/'qa/artifacts/download-corruption-20261110';out=ROOT/'deploy/private-handoff/bluesquare-download-assets-20261110'
if out.exists():raise SystemExit('Immutable asset correction already exists')
rows={int(x['no']):x for x in json.loads((qa/'asset-map.json').read_text(encoding='utf-8-sig'))};assert set(rows)=={34,36,38}
web=out/'web-root/uploads/board';web.mkdir(parents=True);backup=out/'original-backup_DO-NOT-UPLOAD';backup.mkdir()
sha=lambda b:hashlib.sha256(b).hexdigest();receipt={'production_deployed':False,'sql_changes':False,'code_changes':False,'assets':[]}
for no in [38,36]:
 row=rows[no];stored=row['file_attach_1'];assert Path(stored).name==stored and stored.endswith('.pdf')
 src=qa/f'public-after-upload-{no}.bin';reader=PdfReader(src,strict=True);assert not reader.is_encrypted and not reader.get_fields() and not reader.trailer['/Root'].get('/Perms')
 (backup/stored).write_bytes(src.read_bytes());writer=PdfWriter();writer.clone_document_from_reader(reader)
 title=Path(row['file_attach_origin_1']).stem;writer.add_metadata({'/Title':title})
 md=writer.root_object.get('/Metadata')
 if md:
  obj=md.get_object();xml=ET.fromstring(obj.get_data())
  for prefix,uri in [('x','adobe:ns:meta/'),('rdf','http://www.w3.org/1999/02/22-rdf-syntax-ns#'),('dc','http://purl.org/dc/elements/1.1/'),('xmp','http://ns.adobe.com/xap/1.0/')]:ET.register_namespace(prefix,uri)
  nodes=xml.findall('.//{http://purl.org/dc/elements/1.1/}title//{http://www.w3.org/1999/02/22-rdf-syntax-ns#}li');assert nodes
  for node in nodes:node.text=title
  obj.set_data(ET.tostring(xml,encoding='utf-8',xml_declaration=True))
 dest=web/stored
 with dest.open('wb')as f:writer.write(f)
 check=PdfReader(dest,strict=True);assert check.metadata.title==title and len(check.pages)==len(reader.pages)
 if check.trailer['/Root'].get('/Metadata'):
  xml=ET.fromstring(check.trailer['/Root']['/Metadata'].get_object().get_data());assert all(n.text==title for n in xml.findall('.//{http://purl.org/dc/elements/1.1/}title//{http://www.w3.org/1999/02/22-rdf-syntax-ns#}li'))
 for a,b in zip(reader.pages,check.pages):assert a.get_contents().get_data()==b.get_contents().get_data() and a.mediabox==b.mediabox and a.cropbox==b.cropbox
 receipt['assets'].append({'no':no,'stored':stored,'origin':row['file_attach_origin_1'],'title':title,'pages':len(check.pages),'page_streams_and_boxes_unchanged':True,'before_sha256':sha(src.read_bytes()),'after_sha256':sha(dest.read_bytes())})
row=rows[34];stored=row['file_attach_1'];assert Path(stored).name==stored and stored.endswith('.zip');src=qa/'public-after-upload-34.bin';(backup/stored).write_bytes(src.read_bytes());names=[];removed=[]
with zipfile.ZipFile(src)as old,zipfile.ZipFile(web/stored,'w',zipfile.ZIP_DEFLATED,compresslevel=6)as new:
 for entry in old.infolist():
  raw=entry.filename if entry.flag_bits&0x800 else entry.filename.encode('cp437').decode('utf-8')
  name=unicodedata.normalize('NFC',raw);parts=PurePosixPath(name).parts;assert not PurePosixPath(name).is_absolute()and '..'not in parts
  if name.startswith('__MACOSX/')or name.endswith('/.DS_Store')or name=='.DS_Store':removed.append(name);continue
  if entry.is_dir():continue
  data=old.read(entry);assert name.endswith('.pdf')
  new.writestr(name,data);names.append({'path':name,'sha256':sha(data),'bytes':len(data)})
with zipfile.ZipFile(web/stored)as z:
 assert z.testzip()is None and len(z.namelist())==2
 for item in names:
  info=z.getinfo(item['path']);assert info.flag_bits&0x800 and unicodedata.is_normalized('NFC',info.filename)
  assert sha(z.read(info))==item['sha256']
receipt['assets'].append({'no':34,'stored':stored,'origin':row['file_attach_origin_1'],'before_sha256':sha(src.read_bytes()),'after_sha256':sha((web/stored).read_bytes()),'UTF8_NFC_paths':names,'removed_OS_metadata_entries':removed,'business_files_unchanged':True})
(out/'asset-validation.json').write_text(json.dumps(receipt,ensure_ascii=False,indent=2),encoding='utf-8');print(json.dumps({'out':str(out),'assets':[(x['no'],x['stored'])for x in receipt['assets']]},ensure_ascii=False))
