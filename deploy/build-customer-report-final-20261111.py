from pathlib import Path
from xml.sax.saxutils import escape
from reportlab.pdfgen import canvas
from reportlab.platypus import SimpleDocTemplate,Paragraph,Spacer,Table,TableStyle,PageBreak,KeepTogether
from reportlab.lib import colors
from reportlab.lib.styles import ParagraphStyle
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.lib.enums import TA_LEFT
from reportlab.lib.pagesizes import A4
ROOT=Path(__file__).resolve().parents[1]
OUT=ROOT/'output/pdf';OUT.mkdir(parents=True,exist_ok=True)
pdfmetrics.registerFont(TTFont('Malgun','C:/Windows/Fonts/malgun.ttf'))
pdfmetrics.registerFont(TTFont('MalgunBold','C:/Windows/Fonts/malgunbd.ttf'))
NAVY=colors.HexColor('#13253E');TEAL=colors.HexColor('#087E8B');GRAY=colors.HexColor('#536176');LIGHT=colors.HexColor('#EEF3F8');AMBER=colors.HexColor('#A7680A');WHITE=colors.white
W,H=A4;CW=W-88
styles={
 'body':ParagraphStyle('body',fontName='Malgun',fontSize=9.3,leading=15.2,textColor=NAVY,wordWrap='CJK',spaceAfter=7),
 'small':ParagraphStyle('small',fontName='Malgun',fontSize=8.1,leading=12.5,textColor=GRAY,wordWrap='CJK',spaceAfter=5),
 'cell':ParagraphStyle('cell',fontName='Malgun',fontSize=8.2,leading=13,textColor=NAVY,wordWrap='CJK'),
 'headcell':ParagraphStyle('headcell',fontName='MalgunBold',fontSize=8.3,leading=13,textColor=WHITE,wordWrap='CJK'),
 'h1':ParagraphStyle('h1',fontName='MalgunBold',fontSize=21,leading=30,textColor=NAVY,spaceAfter=15,wordWrap='CJK'),
 'h2':ParagraphStyle('h2',fontName='MalgunBold',fontSize=12,leading=19,textColor=TEAL,spaceBefore=12,spaceAfter=8,wordWrap='CJK'),
 'eyebrow':ParagraphStyle('eyebrow',fontName='MalgunBold',fontSize=9,leading=14,textColor=TEAL,spaceAfter=9),
}
def P(text,style='body'):return Paragraph(escape(text).replace('\n','<br/>'),styles[style])
def table(headers,rows,widths=None):
 data=[[P(x,'headcell') for x in headers]]+[[P(str(x),'cell') for x in row] for row in rows]
 t=Table(data,colWidths=widths or [CW/len(headers)]*len(headers),repeatRows=1,hAlign='LEFT')
 t.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,0),NAVY),('VALIGN',(0,0),(-1,-1),'TOP'),('LEFTPADDING',(0,0),(-1,-1),9),('RIGHTPADDING',(0,0),(-1,-1),9),('TOPPADDING',(0,0),(-1,-1),8),('BOTTOMPADDING',(0,0),(-1,-1),8),('LINEBELOW',(0,1),(-1,-1),.4,colors.HexColor('#DCE4EC')),('ROWBACKGROUNDS',(0,1),(-1,-1),[WHITE,colors.HexColor('#F5F8FB')])]))
 return t
story=[]
def page(n,title,intro):
 if story:story.append(PageBreak())
 story.extend([P(f'BLUESQUARE  /  DEVELOPMENT REVIEW  /  {n:02d}','eyebrow'),P(title,'h1'),P(intro)])
def h(text):story.append(P(text,'h2'))
def para(text):story.append(P(text))
def note(text):
 t=Table([[P(text,'small')]],colWidths=[CW]);t.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,-1),LIGHT),('BOX',(0,0),(-1,-1),.5,colors.HexColor('#CCD7E5')),('LEFTPADDING',(0,0),(-1,-1),12),('RIGHTPADDING',(0,0),(-1,-1),12),('TOPPADDING',(0,0),(-1,-1),10),('BOTTOMPADDING',(0,0),(-1,-1),9)]));story.append(t)

page(1,'블루스퀘어 관리자\n보안 개선 반영 보고서','고객 전달용  |  2026.11.11  |  개발 기준 260918')
para('고객 진단 PDF·점검표·개인정보 가이드와 변경 제안서, 이후 요청에 따라 반영한 기능과 검증 범위를 정리합니다. 개발 및 로컬 검증과 운영 최종 확인을 구분합니다.')
h('최종 작업 범위')
story.append(table(['분류','반영 내용'],[
('진단 보완','제안서 17건 및 별도 C등급 공개 API SQL Injection 1건을 포함한 18개 PDF 대조. 기존 C등급 조치 건은 재검증으로 구분'),
('정책 11건','MFA, gate·IP 통제, 개인정보 접속·다운로드 기록, 마스킹, CRUD 이력, 세션, 비밀번호 주기·초기화, 동시접속, 미접속 잠금, 복사 제한'),
('추가 요청','임시비밀번호 자동발급·복사, 본인 변경 강제, MFA 중복 방지, 고객 계정·권한, 기록 UI와 관리자 컴포넌트 개선'),
('이번 누적본','기존 본문 글자 보존, 다운로드 전송·로딩 및 원본 파일명 보완 포함. 성 위치를 추정하지 않는 영문 이름 마스킹 추가')
],[85,CW-85]))
h('합의된 관리자 운영 방식')
para('https://gate.bluesquare.kr/ 요청을 PHP 라우터가 기존 관리자 파일로 처리합니다. 주소에는 /admin 대신 /index.php/... 경로가 사용됩니다. 동일 호스팅·DB를 유지하며 gate 호스트와 허용 IP/CIDR 조건을 함께 검사합니다. 별도 물리 서버·VPN·관리자 API 이관은 변경 제안서의 제외 범위입니다.')
note('이 보고서는 개발·검증 결과와 배포 준비 상태를 설명합니다. 전체 운영 검수 완료 또는 외부 보안 재진단 합격을 의미하지 않습니다. 실제 비밀값과 고객 개인정보는 포함하지 않았습니다.')
page(2,'고객 진단서 대응','고객 진단 PDF 18개를 기준으로 요구와 대응 기능을 연결했습니다.')
rows=[('C1-1 / H1-1 CASE2','공개 API 및 관리자 검색 SQL Injection','값 바인딩·형식검사·검색 컬럼과 정렬 허용목록'),('L1-5','CSRF','상태 변경 POST·CSRF 검증·쿠키 정책'),('L1-7','XSS','문맥별 escape·HTML 허용목록. 기존 font 내부 글자는 보존하되 속성 제거'),('M2-1 / M3-1','악성 업로드·임의 파일 삭제','확장자·실제 MIME·경로 루트·대상 검증'),('M3-1 CASE2','숨김 게시물/API/첨부 접근','사이트·공개상태·대상 접근 통제'),('M3-5 / H3-5 CASE2 / L3-5 CASE3','대관 첨부·공연 비인증 접근·늦은 인증','업무 실행 전 공통 인증·권한 관문'),('M4-2 / L6-1','비밀정보 하드코딩·오류 노출','환경설정 분리·우회계정 제거·운영 오류정보 제한'),('L5-1 / L5-1 CASE2 / L5-2','MFA·비밀번호 복잡도·시도 제한','등록 이메일 MFA·8자/3종·실패5회/15분'),('LL3-2','세션 재발급','로그인·MFA·임시 비밀번호 전환 시 세션과 토큰 교체'),('L5-5 / LL5-4','관리자 노출·불필요 파일','합의 gate+IP 방식·공개admin차단·미사용모듈/백업/로그 노출 통제')]
story.append(table(['진단 코드','원본 요구','대응'],rows,[105,120,CW-225]))
note('공격 변형 전체를 운영에서 재진단한 것은 아닙니다. 코드·자동 QA와 일부 운영 확인 근거를 바탕으로 대응했으며, 최종 운영 경로·권한 검수와 외부 재진단은 별도입니다.')
page(3,'계정·인증·권한','최고관리자는 계정과 권한을 관리하고, 모든 관리자는 업무 CRUD 권한과 무관하게 본인 비밀번호를 변경할 수 있습니다.')
story.append(table(['단계','동작'],[('계정 등록','최고관리자가 ID·이름·인증메일·역할·업무별 권한·유효기간·사유를 입력'),('자동 발급','시스템이 임시 비밀번호 생성. 발급 직후 모달에서 보기·숨기기·ID와 함께 복사. 임시 비밀번호 기본 만료24시간'),('최초 로그인','ID·임시 비밀번호·보안코드 확인 → 등록 이메일로 MFA 자동 발송 → 코드 인증'),('본인 변경','임시 비밀번호 변경 화면에서 새 비밀번호 필수 설정. 임시 상태 해제·세션 ID/활성토큰/CSRF 교체 후 관리자 진입'),('분실 및 일반 변경','최고관리자 재발급 시 기존 인증 무효화·동일 최초변경 절차. 본인 일반 비밀번호 변경 시 로그아웃 유지')],[92,CW-92]))
h('정책과 이력')
para('비밀번호90일 변경, 유휴30분 타임아웃·카운트다운, 동일ID 단일 활성세션을 적용합니다. 90일 미접속 잠금은 로그인 시 평가하며 사전 일괄 배치잠금은 구현하지 않았습니다. 계정 생성/권한 변경에는 사유와 유효기간을 관리하고 변경 전후·작업자·IP를 기록합니다.')
note('원본 초기화 흐름은 변경 후 로그아웃·재로그인입니다. 이후 확정한 추가 요구에 따라 MFA→변경→관리자 진입으로 이어집니다. 일반 로그아웃 후 새 로그인에서는 MFA를 생략하지 않습니다.')
page(4,'개인정보·관리 화면','기록 수집 기능과 운영자가 찾아볼 수 있는 관리 UI를 함께 정리했습니다.')
story.append(table(['항목','반영'],[('개인정보 기록','접속자 ID·일시·IP·수행업무·정보주체/대상·다운로드 사유. 계정·대관 변경 기록 포함'),('작업 이력','성공한 CRUD 및 계정/권한 변경·사유·유효기간·대상 ID 기록. 비밀번호·인증번호·세션 원문 제외'),('로그 UI','기간·유형·대상·키워드 검색, 유형 배지·상세 펼침, 전체건수·20/50/100건·페이지네이션'),('마스킹','목록 이름·ID·이메일 등 적용. 성 구분 없는 영문 이름은 각 단어를 보수 마스킹. 상세는 업무상 필요한 정보와 열람기록'),('Drag/Copy','개인정보 영역 제한, 입력·버튼 조작 예외. 화면캡처나 개발자도구 차단 기능은 아님'),('다운로드','공개·관리자 바이너리 응답 앞 불필요 출력 제거, 대관은 사유·권한·기록 유지. native 다운로드의 무한 로딩 분리'),('기존 자료','카페 드 블루 삭제 유지. PDF 제목2개·ZIP 한글명1개 원본 교체본은 내용 보존 검증 후 별도 제공')],[90,CW-90]))
h('데이터 일관성 보완')
para('대관 테이블이 MyISAM일 경우 삭제 후 장애에서 롤백할 수 없는 한계를 확인했습니다. nb_request InnoDB 전환용 증분 SQL을 별도로 제공합니다. 웹 덮어쓰기와 구분하며 DB 백업·대관 쓰기 중지 시간에 적용해야 합니다. 고객 데이터 삭제나 전체 덤프 재적재는 하지 않습니다.')
page(5,'검증 결과와 한계','실행일과 검증 환경을 구분합니다. 서로 중복되는 테스트 숫자를 합산하지 않습니다.')
import json,re
log=(ROOT/'qa/artifacts/server-release-20261111.log').read_text(encoding='utf-8-sig')
count=len(re.findall(r'^PASS ',log,re.M))
assert log.rstrip().endswith('PHP ROUTER QA PASSED') and not re.search(r'^(FAIL|SKIP|Warning:|Notice:|Fatal error)',log,re.M)
story.append(table(['확인','근거와 범위'],[('이번 Docker 전체 검사',f'2026.11.11 PHP7.4/MySQL8·PHP gate 구성 {count}개 PASS / 0 FAIL. PHP문법·인증/권한·CRUD·업로드/다운로드·기록/기존 화면 회귀'),('추가 회귀','legacy font 원본 글자보존·XSS 차단, 영문이름 경계, HTTPS 최종설정, InnoDB 전환 데이터보존/반복적용/롤백'),('MFA·클라이언트','이번 등록메일 자동발송·임시변경 전환 HTTP39 및 클라이언트144개 검사. 로컬 SMTPstub 사용, 고객 메일 새 발송 아님'),('기존 운영 확인','11월10일 공개 다운로드6개 무결성·184번 DB 원본일치 확인. 11월11일 gate HTTP→HTTPS, TLS검증·로그인화면200, 공개admin은 가비아404 안내 이동, 공개메인200 확인'),('실제 브라우저','기존 일부 운영 CRUD·에디터·모달 및 로컬 로그 UI 검수 이력. 이번 최신본 모든화면·모바일·고객권한 조합 전수확인은 미완료')],[100,CW-100]))
h('완료로 표시하지 않은 항목')
para('실서버 ZIP·원본자료 교체·SQL 적용은 운영자가 수행해야 합니다. 기관별 실제 IP·고객 계정별 권한, 테스트IP/QA계정 정리, 개인정보 기록 보관·백업·파기 운영증빙은 별도 확인이 필요합니다.')
para('기술검토_블루스퀘어.pdf의 AWS 서비스명은 sohyangtheater-web-prd입니다. 가비아 블루스퀘어 대상과 일치하는지 확인 전 해당 AWS TLS 정책 수정 완료로 표기하지 않습니다.')
page(6,'배포 및 운영 인계','기존 서비스와 DB를 유지하며, 이번 누적본은 이미 보안 업데이트된 운영 사이트용입니다.')
story.append(table(['순서','운영 작업'],[('1. 백업','현재 FTP의 교체 대상과 DB를 새로 백업. 기존 전체 파일·uploads 삭제 금지'),('2. SQL','대관 InnoDB 전환 SQL은 별도 유지보수 시간에 적용. 원본행·컬럼 보존/반복 실행 사본검증 완료. 전체 덤프 재적재·계정 재초기화 없음'),('3. 웹 반영','01 누적수정 ZIP의 내용물을 웹루트 같은 경로에 Binary 덮어쓰기. 후속 본문·마스킹·기록 UI·다운로드 수정 포함'),('4. 원본 파일','02 원본교체 ZIP은 지정 uploads/board 3개만 교체. 기존 자료 별도백업'),('5. HTTPS 최종 설정','기존DB/SMTP 비밀값 유지. HTTP와 legacy admin 예외 false, Secure 쿠키 true, gate강제/MFA true. 기관IP 접근 확인 뒤 테스트IP만 회수'),('6. 확인·복구','gate로그인/MFA·고객권한·저장/업로드/다운로드·기록·공개서비스 확인. 문제 시 교체파일 복구. DB 역방향엔진전환은 자동실행하지 않음')],[95,CW-95]))
h('원본 및 운영 협조')
para('근거: 블루스퀘어 진단 PDF18개, 점검 결과_0417.xlsx, NPG03 마스킹 가이드, 블루스퀘어 보안 개발 제안서(정책19·20·21·23·26~32), 후속 요청. 고객의 자산·자료파기·SNS동의·처리방침·단말·YAMS·공용PC·소화설비·출입대장9건은 고객 운영 이행 범위로 구분합니다.')
note('이번 보고서의 운영 미확인 항목은 배포 후 증빙으로 갱신합니다. 파일이나 기능 존재만으로 고객 과업 전체 완료를 선언하지 않습니다.')
def footer(c,doc):
 c.saveState();c.setStrokeColor(colors.HexColor('#DCE4EC'));c.line(44,39,W-44,39);c.setFont('Malgun',8);c.setFillColor(GRAY);c.drawString(44,25,'NINEONELABS | BLUESQUARE | 2026.11.11');c.drawRightString(W-44,25,str(doc.page));c.restoreState()
path=OUT/'블루스퀘어_보안개선_개발반영보고서_20261111.pdf'
doc=SimpleDocTemplate(str(path),pagesize=A4,rightMargin=44,leftMargin=44,topMargin=40,bottomMargin=55,title='블루스퀘어 보안 개선 개발 반영 보고서',author='NINEONELABS')
doc.build(story,onFirstPage=footer,onLaterPages=footer)
print(str(path))
