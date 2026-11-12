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

page(1,'블루스퀘어 관리자\n보안 개선 개발 반영 보고서','고객 확인용  |  작성일 2026.11.10  |  개발 기준 260918 · v7')
para('고객 제공 보안 진단서·개인정보 가이드·점검표와 추가 요청을 기준으로, 현재 코드에 반영한 기능과 검증 결과를 정리했습니다. 기존 서비스 파일·데이터는 유지하며 관리자 보안과 관리 화면을 개선합니다.')
h('확정한 운영 방식')
para('관리자 주소는 https://gate.bluesquare.kr/ 입니다. 가비아의 기존 호스팅과 DB를 사용하고 PHP에서 gate 요청을 관리자 화면으로 처리합니다. gate 서브도메인과 허용 IP/CIDR 방식은 합의된 구성입니다. 별도 물리 서버 또는 VPN을 구축했다는 의미는 아닙니다.')
para('임시 비밀번호는 추가 요청 범위입니다. 신규 계정 또는 재발급 계정은 로그인 → MFA → 본인 비밀번호 변경 → 관리자 진입으로 이어집니다. 이 변경 과정에서 완료한 MFA를 반복하지 않도록 구성했습니다.')
h('개발·반영·검증 상태')
story.append(table(['구분','현재 상태'],[
 ('이번 보완','로그 검색·필터·페이지네이션·유형 배지, 개인정보 변경 기록, 게시판 목록 출력·마스킹, 일괄 삭제 대상 기록'),
 ('자동 QA','Docker PHP·HTTP 702건 / 클라이언트 VM 130건 / MFA 활성 HTTP 24건 통과. 각 실패 0건'),
 ('실제 화면','로컬 Chrome 작업 상세 펼침·페이지 이동·삭제 필터 및 개인정보 화면 표시 확인'),
 ('운영 서버','v6 반영은 사용자 보고. v7은 업로드 준비본이며 운영 반영·재확인 전'),
 ('남은 확인','모바일 시각 검수, v7 운영 확인, 고객 계정별 권한·기관 IP, 고객 운영 조치')
],[95,CW-95]))
note('이 문서는 개발 반영 및 검증 범위 설명서입니다. 고객 운영 조치 이행증빙이나 외부 보안 재진단 합격서를 대신하지 않습니다. 비밀번호·SMTP·DB 비밀값·실제 허용 IP·개인정보 원문은 포함하지 않았습니다.')
page(2,'계정·권한과 인증','최고관리자는 계정과 업무 권한을 관리하고, 각 관리자는 본인의 비밀번호를 직접 변경합니다.')
h('신규 등록과 임시 비밀번호')
story.append(table(['순서','처리'],[
 ('1. 최고관리자 등록','아이디·이름·인증 이메일·활성 상태·역할·메뉴별 CRUD·권한 유효기간·변경 사유를 입력'),
 ('2. 시스템 자동 발급','임시 비밀번호를 생성하고 계정정보 모달에서 보기·숨기기·복사 제공. 아이디와 임시 비밀번호를 담당자에게 전달'),
 ('3. 담당자 최초 접속','아이디·임시 비밀번호 → 이메일 MFA → 새 비밀번호 필수 변경'),
 ('4. 정상 상태 전환','시스템이 임시 상태를 해제하고 세션 ID·활성 토큰·CSRF를 교체하여 관리자 진입'),
 ('5. 분실 시','최고관리자가 임시 비밀번호 재발급. 이전 인증 무효화 후 같은 본인 변경 절차 진행')
],[110,CW-110]))
h('권한과 이력')
para('계정 생성·권한 변경·삭제에는 변경 사유를 요구합니다. 대상 계정·소유자·역할·메뉴별 권한·유효기간의 변경 전후를 작업 이력에 남깁니다. 최고관리자는 최고관리자 생성이 가능하며, 마지막 유효 최고관리자 제거를 막는 통제를 적용합니다.')
para('본인 비밀번호 변경은 업무 CRUD 권한과 별개입니다. 상단 비밀번호 변경 링크와 사이트정보관리의 비밀번호 변경 메뉴에서 접근합니다. 일반 비밀번호 변경은 현재 비밀번호 확인 후 로그아웃하며, 임시 비밀번호 변경만 MFA 인증을 이어받습니다.')
h('기본 정책')
para('로그인 실패 5회/15분 잠금, MFA 6자리/15분 만료/5회 검증/재발송 30초, 유휴 세션 30분, 비밀번호 변경주기 90일, 장기 미접속 90일 잠금, 동일 ID 새 로그인 시 이전 세션 종료. 장기 미접속 잠금은 로그인 시점에 평가합니다.')
page(3,'개인정보 보호와 기록 화면','개인정보 열람 기록과 업무 변경 이력을 구분하되, 실제 처리 대상과 작업자를 연결합니다.')
h('개인정보 처리')
para('계정·대관 목록의 이름·아이디·이메일·전화번호를 NPG 03 기준 공통 함수로 마스킹합니다. 게시판 관리자 목록의 작성자도 마스킹하고 열람 대상 ID를 기록합니다. 업무상 필요한 상세 열람은 권한을 확인하고 기록합니다.')
para('대관 첨부·개인정보 다운로드는 사유를 입력하고 작업자·시각·IP·대상·사유를 기록합니다. 개인정보 영역에는 Drag/Copy 제한을 적용하되 입력창은 조작할 수 있도록 유지합니다. 화면 촬영·개발자도구 사용을 막는 기능은 아닙니다.')
story.append(table(['항목','작업 이력','개인정보 접속기록'],[
 ('수집 범위','성공한 생성·수정·삭제 및 계정·권한 변경','계정·대관 열람/다운로드와 계정 변경·대관 입력/삭제, 게시판 작성자 목록 열람'),
 ('표시','일시·작업자 ID·IP·유형·대상·업무 내용','일시·작업자 ID·IP·유형·정보주체·업무·사유'),
 ('공통 UI','기간·대상·유형·검색어, 전체 건수, 20/50/100건, 페이지 이동','같은 공통 조회·필터·페이지 컴포넌트'),
 ('상세','계정 권한 전후·사유 펼침, 일괄 삭제 대상 ID','마스킹된 정보주체, 다운로드 사유'),
 ('열람 권한','최고관리자 한정','최고관리자 한정')
],[67,(CW-67)/2,(CW-67)/2]))
h('기록 실패 시 처리')
para('계정 변경은 개인정보 기록과 작업 이력을 같은 트랜잭션으로 처리하여 기록 실패 시 계정 변경도 되돌립니다. 대관은 기록 저장 실패를 먼저 차단하고, 구형 MyISAM 등록 후 대상 연결 실패 시 새 행·신규 첨부만 보상 정리합니다.')
note('MyISAM 대관 테이블은 삭제 직후 DB 연결 단절 등 장애까지 트랜잭션으로 복원할 수 없습니다. 보관·백업·복구 정책은 운영에서 관리해야 합니다. 영문 이름은 성을 마지막 단어로 가정하고 국제번호는 끝 4자리만 표시합니다.')
page(4,'진단 PDF 대응 ①','문서 폴더 17건과 별도 C등급 1건, 총 18건을 기준으로 대응 항목을 관리합니다.')
story.append(table(['진단','대응 내용'],[
 ('C 1-1 (1)','공개 API 입력 바인딩·정수 검증으로 SQL Injection 대응'),
 ('H 1-1 CASE2','관리자 검색 컬럼·정렬 허용목록과 prepared statement'),
 ('H 3-5 CASE2','공연 AJAX 처리 이전 관리자 인증·권한·메서드 검증'),
 ('M 2-1 (1)','업로드 확장자·실제 MIME·파일명 검증, 업로드 실행 차단'),
 ('M 3-1 (1)','첨부 삭제의 업로드 루트 내부 경로 검증·참조 파일 보호'),
 ('M 3-1 CASE2','숨김 게시물의 공개 API·상세·첨부 접근 경계 보완'),
 ('M 3-5 (1)','대관 첨부 관리자 인증·권한·대상·사유·다운로드 기록'),
 ('M 4-2 (1)','우회 계정·하드코딩 DB/SMTP 비밀값 제거, 환경설정 분리'),
 ('L 1-5 (1)','상태 변경 POST에 CSRF 검증·세션 쿠키 정책 적용')
],[110,CW-110]))
h('검증 범위 해석')
para('위 대응은 코드와 로컬 자동 QA, 기존 실서버 검수 기록을 근거로 합니다. 모든 진단 원문 이미지의 공격 변형을 현재 운영 서버에서 다시 재현했다는 뜻은 아닙니다. v7 업로드 후 운영 확인을 별도 진행합니다.')
page(5,'진단 PDF 대응 ②','합의된 운영 방식과 추가 요구사항을 원본 대응 항목과 함께 구분하여 기록합니다.')
story.append(table(['진단','대응 내용'],[
 ('L 1-7 (1)','출력 escape·제한 태그 sanitize. 이번에 게시판 목록의 재디코딩 출력 경로 추가 보완'),
 ('L 3-5 CASE3','업무·DB 처리 이전 공통 관리자 인증 관문 적용'),
 ('L 5-1 (1)','등록 이메일 결속 MFA, 만료·시도 횟수·재발송 간격 통제'),
 ('L 5-1 CASE2','8자 이상·문자군 3종 검증, password_hash 저장·기존 해시 전환'),
 ('L 5-2 (1)','실패 횟수 누적·15분 잠금·만료 시 재시도'),
 ('L 5-5 (1)','합의된 gate 서브도메인과 허용 IP/CIDR 통제. 기존 가비아 파일·DB 공유'),
 ('L 6-1 (1)','운영 오류 SQL·경로·내부 상세 비노출, 일관된 오류 응답'),
 ('LL 3-2 (1)','로그인·MFA·임시 비밀번호 전환의 세션 파기·재발급'),
 ('LL 5-4 (1)','불필요 구형 모듈 정리, 백업·SQL·로그·비밀파일 웹 접근/배포 제외')
],[110,CW-110]))
h('점검표 정책 연결')
para('정책 21은 개인정보 수행업무·정보주체 기록, 23은 마스킹, 26은 계정·권한 변경 사유/전후/유효기간, 27은 세션 만료, 28은 비밀번호 주기, 29는 초기화 후 본인 변경, 30은 동시접속 제한, 31은 장기 미접속 잠금, 32는 Drag/Copy 제한으로 관리합니다.')
para('21·26~31은 대학로 점검표의 상세 기준을 사용자 요청에 따라 블루스퀘어에도 확대 적용한 범위입니다. 정책29의 재로그인 기본 흐름은 추가 요청한 임시 비밀번호 인증 연속 흐름으로 조정했습니다.')
page(6,'QA 결과와 배포 안내','이번 로컬 변경은 웹 파일만 적용하며 운영 DB·계정·환경설정을 초기화하지 않습니다.')
story.append(table(['검증','결과와 범위'],[
 ('Docker PHP·HTTP','702 PASS / 실패 0. 문법·공개/관리자 CRUD·권한·라우팅·세션·업로드·로그·실패 롤백 포함'),
 ('클라이언트 VM','130건 통과. 로딩 해제·폼 상태·모달·요청/경로 회귀. 실제 브라우저 검증과 구분'),
 ('MFA 활성 HTTP','24건 통과. MFA→임시 변경→관리자, 세션·토큰·CSRF 교체, 재사용/동시로그인 방어'),
 ('새 로그 HTTP','로그 UI45건, 대관 개인정보9건, 계정 개인정보11건. 위702건에 포함'),
 ('로컬 Chrome','작업 상세 펼침·2페이지 이동·삭제 필터 확인. 개인정보 새 화면 표시 확인'),
 ('미확인','브라우저 제어 지연으로 개인정보 검색 제출 후 상태·모바일 시각 검수 미완료. 운영 v7 재검수 대기')
],[110,CW-110]))
h('적용 순서')
para('1. 현재 운영 웹 파일과 DB 백업 → 2. 제공 누적 수정 ZIP을 PC에서 해제 → 3. 기존 웹 루트에 폴더 구조 그대로 바이너리 전송·덮어쓰기 → 4. 업로드 완료 후 새로고침 → 5. gate 로그인·MFA·본인 비밀번호·로그 검색·CRUD·공개 반영 확인 순서로 진행합니다.')
para('이번 변경에는 SQL 실행·.env 교체·계정 재생성이 없습니다. 기존 uploads 또는 웹 전체를 삭제하지 않습니다. 전체 웹 보관 ZIP과 검증용 로그·QA·문서·SQL을 웹 루트에 올리지 않습니다.')
note('v7 운영 반영 후 문제가 발견되면 이번 수정 파일만 사전 백업본으로 되돌립니다. 고객 데이터가 변경된 이후 전체 DB를 과거 덤프로 덮어쓰는 복원은 하지 않습니다.')
page(7,'고객 운영 확인과 인계 항목','개발 코드로 처리할 수 있는 항목과 고객 조직·시설·운영에서 완료할 항목을 분리합니다.')
h('서비스 운영 확인')
para('고객 제공 관리자별 권한·이메일·활성/유효기간을 최종 대조하고 기관별 허용 IP의 실제 접속을 확인합니다. gate SSL 적용 후 HTTP 및 기존 /admin 예외의 허용 범위를 확정하고 불필요한 임시 테스트 IP를 회수합니다. 실서버 QA 전용 비활성 계정이 남아 있는지도 확인합니다.')
h('고객 운영 조치 9건')
story.append(table(['번호','고객 확인 내용'],[
 ('18','단말·티켓박스 자산관리 및 YAMS 등록'),('19','대관 자료·미선정 건의 보관기간과 파기 절차'),('20','SNS 이벤트의 수집 안내·동의·이력'),('21','개인정보처리방침의 가비아 재위탁·이벤트 항목 현행화'),('22','Windows 단말 환경 개선'),('23','YAMS 사용자 현행화'),('24','모니터 비밀번호 부착 금지·공용 PC 관리'),('25','MDF실 소화설비'),('26','MDF실 출입·작업대장')
],[48,CW-48]))
h('별도 운영 증빙')
para('로그 보관기간·백업·정기점검, 과거 비밀값 교체, 개인정보 파기 이행은 고객 정책과 운영 증빙으로 확인합니다. 기술검토서의 AWS 대상 명칭이 현재 가비아 블루스퀘어와 일치하는지는 미확인으로 남기며 임의의 AWS 설정을 변경하지 않았습니다.')
para('상세 근거는 고객원본 요구대조 체크리스트와 v7 QA 기록에 보관합니다. 최종 운영 확인 결과를 기록한 후 고객에게 완료 상태를 확정 안내합니다.')
class NumberedCanvas(canvas.Canvas):
 def __init__(self,*args,**kwargs):super().__init__(*args,**kwargs);self._states=[]
 def showPage(self):self._states.append(dict(self.__dict__));self._startPage()
 def save(self):
  total=len(self._states)
  for state in self._states:
   self.__dict__.update(state);self.setStrokeColor(colors.HexColor('#DAE2EC'));self.line(44,43,W-44,43);self.setFont('Malgun',7.6);self.setFillColor(GRAY);self.drawString(44,29,'NINEONELABS  |  블루스퀘어  |  개발 반영 보고서');self.drawRightString(W-44,29,f'{self._pageNumber} / {total}');super().showPage()
  super().save()
def header(c,doc):
 c.setFillColor(TEAL);c.rect(0,H-12,W,12,fill=1,stroke=0);c.setFont('Malgun',7.7);c.setFillColor(GRAY);c.drawRightString(W-44,H-33,'2026.11.10  |  260918 v7')
p=OUT/'블루스퀘어_보안개선_개발반영보고서_v7_20261110.pdf'
doc=SimpleDocTemplate(str(p),pagesize=A4,leftMargin=44,rightMargin=44,topMargin=54,bottomMargin=56,title='블루스퀘어 관리자 보안 개선 개발 반영 보고서 v7',author='나인원랩스')
doc.build(story,onFirstPage=header,onLaterPages=header,canvasmaker=NumberedCanvas)
print(p)
