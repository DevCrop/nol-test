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
page(1,'블루스퀘어 관리자\n보안 개선 개발 보고서','고객 검토용 초안  v0.1  |  작성일 2026.11.10  |  개발 기준 v6')
para('기존 홈페이지와 운영 데이터를 유지하면서 관리자 인증, 계정·권한, 개인정보 보호, 작업 기록 및 gate 접속 구조를 개선한 내용을 정리했습니다. 구현과 검증의 범위를 구분하고, 최종 전달 전에 보완할 항목을 함께 제시합니다.')
h('현재 상태')
story.append(table(['구분','확인 내용'],[
 ('개발 반영','계정·권한·MFA·세션·개인정보 보호 기반 및 v6 후속 결함 수정'),
 ('서버 반영','v6 업로드 완료는 사용자 보고로 확인. v6 적용 후 독립 운영 회귀는 별도'),
 ('자동 검증','Docker PHP·HTTP 632 PASS / 클라이언트 VM 130 PASS, 실패 0'),
 ('추가 개선','작업이력·개인정보 접속기록의 검색·필터·페이지네이션 및 표시 방식'),
 ('최종 수용','모든 모달·권한별 실제 로그인·모바일·운영 정책 확인 후 최종본 확정')
],[100,CW-100]))
h('문서 이용 안내')
para('이 문서는 개발 적용 설명과 남은 확인사항을 공유하기 위한 초안입니다. 보안 진단의 재진단 합격서나 전체 기능에 대한 무오류 보증서가 아닙니다. 미완료 항목을 처리하고 운영 확인 결과를 반영한 뒤 최종 완료 보고서로 확정합니다.')
note('포함하지 않은 정보: 실제 비밀번호·임시 비밀번호·SMTP 앱 비밀번호·DB 접속값·허용 IP 목록·고객 개인정보 원문. 해당 정보는 별도 비공개 운영 자료로 관리합니다.')
page(2,'검토 기준과 반영 범위','고객 제공 진단서와 개인정보 가이드, 점검표 및 실제 개발 코드·QA 증거를 기준으로 대조했습니다.')
story.append(table(['근거','이번 문서의 처리'],[
 ('블루스퀘어 진단서 18건','문서 폴더의 17건 + C등급 진단서 1건. 항목별 대응은 6~7쪽에 정리'),
 ('NPG 03 개인정보 가이드','대량 목록 마스킹, 업무상 필요한 상세 열람, Copy·Drag 제한 기준'),
 ('점검 결과_0417.xlsx','블루 시트와 대학로 시트의 요구를 구분. 정책 번호를 서로 혼용하지 않음'),
 ('기술검토서','AWS 대상 명칭이 현재 가비아 블루스퀘어와 일치하는지 미확인. 직접 조치 완료로 산정하지 않음'),
 ('개발·검증 기록','v6 소스/배포 manifest, 실제 Docker·HTTP 결과, 실서버 Chrome QA 기록')
],[125,CW-125]))
h('원문과 최종 구현을 구분하는 사항')
para('관리자 주소는 gate.bluesquare.kr이며 기존 호스팅의 파일과 DB를 공유하는 PHP 라우팅 방식입니다. 별도 물리 서버 분리 또는 VPN 구축과 동일한 구성이 아닙니다. 진단서의 분리 요구와 현재 대체 통제의 수용 범위는 보안 담당자와 최종 확인합니다.')
para('초기 임시 비밀번호 변경 후 재로그인하던 흐름은 후속 개발 요청에 따라, 완료한 MFA 인증을 유지하고 비밀번호 변경 후 관리자 화면으로 이어지도록 조정했습니다. 새 세션·인증 토큰·CSRF를 발급하며, 일반 비밀번호 변경과 새 로그인에는 기존 재인증 정책을 유지합니다.')
h('범위 해석')
para('블루 시트의 개인정보 마스킹·Drag/Copy 요구와 별도로, 대학로 점검표의 계정·세션·이력 상세 정책을 공통 운영 방식으로 블루에도 확장했습니다. 이 확대 적용을 블루 원문 시트에 동일하게 적혀 있었다고 설명하지 않습니다.')
note('로그 화면의 필터·검색·페이지네이션은 사용자의 UI 개선 요청에 따른 보완 항목입니다. 기록 수집 요구와 화면 편의성 요구를 구분합니다.')
page(3,'관리자 계정과 인증 흐름','최고관리자는 계정과 메뉴별 권한을 관리하며, 각 관리자는 본인 비밀번호를 직접 관리합니다.')
h('임시 비밀번호 최초 로그인')
story.append(table(['1','2','3','4'],[('아이디·임시 비밀번호\n로그인','이메일 MFA\n인증 완료','본인 새 비밀번호\n필수 변경','인증 상태 갱신 후\n관리자 진입')]))
para('신규 계정의 비밀번호는 시스템이 자동 발급합니다. 최고관리자는 발급 직후 계정정보 모달에서 보기·숨기기·복사할 수 있으며, 평문 비밀번호를 목록에서 영구 조회하는 구조는 아닙니다. 임시 비밀번호 유효기간 기본값은 24시간입니다.')
h('비밀번호 변경 메뉴 위치')
para('관리자 상단의 비밀번호 변경 링크와 사이트정보관리 하위의 비밀번호 변경 메뉴가 같은 화면으로 연결됩니다. 본인 비밀번호 변경은 업무 메뉴의 조회·등록·수정·삭제 권한과 별개로 허용합니다.')
para('일반 비밀번호 변경은 현재 비밀번호를 확인한 뒤 새 비밀번호를 저장하고 로그아웃합니다. 비밀번호 분실 시 최고관리자가 임시 비밀번호를 재발급하며, 기존 인증을 무효화하고 당사자가 다시 본인 비밀번호로 변경합니다.')
h('주요 정책 기본값')
story.append(table(['항목','설정'],[
 ('로그인 실패 / MFA','로그인 실패 5회 시 15분 잠금. MFA 6자리·15분 만료·검증 5회·재발송 간격 30초'),
 ('세션 / 비밀번호','유휴 30분, 비밀번호 90일 변경주기, 동일 계정 신규 로그인 시 기존 인증 종료'),
 ('계정 통제','활성·비활성, 권한 시작/종료, 장기 미접속 90일 잠금, 변경 사유 기록'),
 ('메뉴 권한','8개 메뉴별 조회·등록·수정·삭제. 서버에서 권한을 재검증')
],[115,CW-115]))
page(4,'개인정보와 기록 관리','개인정보의 표시, 열람 및 다운로드를 구분하고 누가 어떤 업무를 수행했는지 추적할 수 있도록 구성했습니다.')
h('개인정보 표시와 다운로드')
para('대량 목록에는 이름·아이디·이메일·전화번호 마스킹 함수를 사용합니다. 상세 화면은 업무상 필요한 값만 허용하고 열람 기록을 남깁니다. 개인정보 영역의 복사·드래그 제한은 입력창과 접근성 조작을 방해하지 않도록 예외를 둡니다. 화면 촬영이나 개발자도구 사용까지 방지하는 기능은 아닙니다.')
para('대관 첨부 다운로드는 사유 입력을 요구하고 관리자·시각·대상·사유·IP를 기록합니다. 실서버 브라우저에서 빈 사유와 5자 미만 입력이 거부되는 것을 확인했습니다. 실제 고객 첨부 다운로드는 이번 실서버 검수에서 실행하지 않았습니다.')
h('두 기록 화면의 차이')
story.append(table(['구분','작업 이력','개인정보 접속기록'],[
 ('목적','성공한 생성·수정·삭제 추적','개인정보 열람·다운로드 추적'),
 ('주요 표시','일시·작업자 ID·IP·작업·대상·내용','일시·열람자 ID·IP·유형·정보주체·업무·사유'),
 ('상세','계정·권한 변경 사유 및 전후 내용 펼침','대상 이름은 기록 단계에서 마스킹'),
 ('열람 권한','최고관리자 한정','최고관리자 한정'),
 ('현재 화면 범위','최근 200건 고정 조회','최근 200건 고정 조회')
],[78,(CW-78)/2,(CW-78)/2]))
h('가이드 대조 범위')
para('NPG 03의 한글 이름·아이디·이메일·국내 전화 핵심 규칙과 현재 공통 함수를 대조했습니다. 영문 이름은 성을 마지막 단어로 보는 구현 가정이 있으며 국제전화는 마지막 4자리만 남기는 보수적 처리입니다. 모든 국가·이름 형식이 가이드 예시와 동일하다고 단정하지 않습니다.')
h('고객 문의에 대한 안내')
para('작업자·열람자는 로그인한 관리자 ID입니다. 과거 기록에 ID가 없으면 화면에 -가 표시되며 임의로 특정 사용자로 채우지 않습니다. 사유는 주로 개인정보 다운로드의 업무 목적을 남기기 위한 값이고, 일반 열람에서 사유가 없는 기록은 -로 표시될 수 있습니다.')
note('기록 보관기간·정기 점검·백업 주기·담당자 지정은 운영 절차와 함께 확정해야 합니다. 화면이 200건만 표시된다는 사실은 DB의 오래된 기록이 자동 삭제된다는 뜻이 아닙니다.')
page(5,'로그 UI 추가 개선안','현재 코드 대조에서 확인한 미반영 사항입니다. 이 페이지는 구현 완료 내역이 아닌 다음 작업 범위입니다.')
story.append(table(['항목','현재','개선 방향'],[
 ('기간·대상·유형 필터','없음','기간, 작업/열람 유형, 업무 대상별 필터'),
 ('검색','없음','작업자 ID·대상·업무/사유 검색. 입력 검증과 파라미터 바인딩'),
 ('건수·페이지 이동','최신 200건 고정','전체 건수·페이지당 건수·페이지 이동. 필터를 유지하며 이동'),
 ('작업 구분','생성·수정·삭제 텍스트','기존 관리자 CSS로 색상·배지와 문구를 함께 사용'),
 ('개인정보 유형','view / download 영문','열람 / 다운로드 한글 표기와 일관된 배지'),
 ('상세 내용','비계정 항목 JSON 원문','업무용 문구·필드명으로 정리. 필요한 상세만 펼침'),
 ('빈 결과·긴 내용','기본 표 중심','검색 결과 없음·초기 빈 상태·긴 사유 줄바꿈 개선'),
 ('컴포넌트 일관성','기본 관리자 표 사용','놀의 공통 패턴을 참고하되 블루의 데이터/권한 구조에 맞게 재사용')
],[85,115,CW-200]))
h('완료 판정에 필요한 검증')
para('0건·1건·여러 페이지 데이터, 잘못된 날짜·검색어, 필터 왕복·페이지 이동·새로고침, 상세 펼침·접기, 모바일 가로 넘침, 일반관리자의 접근 거부를 확인합니다. 화면 문구 변경이 기존 로그 원문이나 개인정보 마스킹 정책을 변경하지 않아야 합니다.')
note('별도 로그 내보내기 기능은 현재 개발 완료 항목이 아닙니다. 추가한다면 개인정보 포함 여부·권한·다운로드 사유·감사 기록을 함께 설계합니다.')
page(6,'취약점 진단 대응  1 / 2','진단서 18건을 항목별로 추적합니다. 아래는 코드·개발 QA의 대응 내역이며 고객 재진단 합격을 의미하지 않습니다.')
rows1=[
 ('C 1-1 (1)','공개 API SQL Injection','사용자 입력 바인딩, 정수/날짜 검증, 동적 컬럼 허용목록'),
 ('H 1-1 CASE.2','관리자 SQL Injection','검색 컬럼·정렬 허용목록 및 prepared statement'),
 ('H 3-5 CASE.2','공연 관리 인증 우회','DB 작업 전 관리자 인증·권한 및 상태 변경 메서드 검사'),
 ('M 2-1 (1)','파일 업로드','확장자·실제 MIME 검사, 서버 파일명, 업로드 실행 차단'),
 ('M 3-1 (1)','경로 조작 삭제','허용 업로드 루트 내 경로만 삭제, 외부 경로 거부'),
 ('M 3-1 CASE.2','숨김 자료 노출','공개 API·상세·첨부의 노출 상태 및 대상 검증'),
 ('M 3-5 (1)','대관 첨부 접근','인증·권한·대상 검증 및 사유 기록을 거친 다운로드'),
 ('M 4-2 (1)','소스 내 비밀정보','우회 계정 제거, DB·SMTP 설정 분리, 운영 비밀값 비공개'),
 ('L 1-5 (1)','CSRF','상태 변경 POST의 CSRF 검사, 쿠키 SameSite와 권한 재검증')
]
story.append(table(['진단 ID','영역','개발 반영 내용'],rows1,[100,110,CW-210]))
h('운영 측 확인')
para('과거에 사용한 자격정보의 폐기·교체와 원문 공격 화면의 모든 변형 payload 재현은 코드 변경만으로 완료되는 항목이 아닙니다. 운영 서버의 실제 설정·차단 응답·재진단 결과를 별도로 확인합니다.')
page(7,'취약점 진단 대응  2 / 2','인증 정책·출력 보호·운영 노출 통제를 함께 적용합니다.')
rows2=[
 ('L 1-7 (1)','XSS','출력 escape, 제한된 HTML 처리, 제목·속성·textarea 경계 검증'),
 ('L 3-5 CASE.3','인증 누락 경로','관리자 HTML/AJAX 공통 인증 관문과 권한 검증'),
 ('L 5-1 (1)','추가 인증','이메일 MFA와 만료·오류횟수·재발송 간격 적용'),
 ('L 5-1 CASE.2','비밀번호 정책','8자 이상·문자군 3종, 해시 저장, 기존 해시 호환 전환'),
 ('L 5-2 (1)','로그인 시도 제한','실패 누적·잠금·만료, 인증 상태 검증'),
 ('L 5-5 (1)','관리자 접근 통제','gate 호스트 및 IP/CIDR. 원문 서버분리/VPN과는 구성 차이 확인 필요'),
 ('L 6-1 (1)','오류정보 노출','운영 상세 오류 비노출, 예측 가능한 AJAX 오류 응답'),
 ('LL 3-2 (1)','세션 고정','로그인·MFA·임시비밀번호 변경 전환 시 인증 상태 교체'),
 ('LL 5-4 (1)','불필요 파일 노출','미사용 모듈 정리, SQL·테스트·로그·배포 자료의 웹 접근/배포 통제')
]
story.append(table(['진단 ID','영역','개발 반영 내용'],rows2,[100,110,CW-210]))
h('기술검토서 적용 주의')
para('기술검토서의 AWS 서비스 명칭은 sohyangtheater-web-prd로 확인됩니다. 현재 가비아 블루스퀘어의 실제 TLS 대상을 확인하지 않은 채 해당 AWS TLS 정책 교체를 완료로 기록하지 않습니다. gate SSL 설치 보고와 이 기술검토서의 조치는 구분합니다.')
page(8,'후속 결함 수정과 QA 결과','v6는 실제 관리자 QA에서 확인한 결함을 기존 보안 업데이트에 누적 반영한 웹 수정본입니다.')
story.append(table(['수정 영역','적용 내용'],[
 ('게시판','공지 체크 복원/해제, 기존 링크 URL 복원, 추가 필드의 반복 인코딩 방지'),
 ('gate 라우터','폼 입력값·본문을 경로 재작성에서 보호. 실제 화면/자산/처리기 URL 라우팅 유지'),
 ('팝업','신규 저장 실패, 링크없음의 기존 DB 호환, 공개 노출기간·타겟·순위 반영'),
 ('배너·날짜','신규 순위 입력 존중, 무기한/기한 왕복, 링크 라디오와 날짜 상태 충돌 제거'),
 ('문구','배너/팝업 등록 버튼과 화면 제목·순위 안내 정리')
],[105,CW-105]))
h('검증 증거의 범위')
para('Docker PHP 7.4·MySQL 8의 격리 DB에서 PHP/실제 HTTP 632건 통과, 클라이언트 VM 130건 통과, 실패 0건입니다. 관리자 저장 요청과 공개 홈/상세 출력의 연결, 파일 수명·오류 시 보존·권한·폼 값 보존을 포함합니다. VM 검증은 브라우저 렌더링 검수와 구분합니다.')
para('실서버에서는 승인받은 QA 게시판·배너·공연을 생성하여 등록/수정/업로드/공개 반영/숨김·삭제를 확인했습니다. 계정 모달 보기·숨기기·복사와 32개 권한 체크 연동, 기간 오류 거부도 확인했습니다. 실제 고객 자료는 임의 수정·삭제하지 않았습니다.')
note('이후 v6 서버 반영 완료는 사용자 보고입니다. v6 적용 후 모든 모달·모바일·권한별 로그인·실제 30분 만료·동시접속을 독립적으로 재검증한 상태는 아닙니다. 자동 통과 수치를 운영 전체 검수 완료로 대체하지 않습니다.')
page(9,'최종 확정 전 작업과 운영 인계','추가 개발·실운영 확인·고객 운영조치를 분리해 마무리합니다.')
story.append(table(['우선순위','다음 작업','완료 기준'],[
 ('1. 개발','작업이력·개인정보 접속기록 UI 개선','필터·검색·페이지네이션·상세/배지·빈 상태의 코드/화면 QA'),
 ('2. 운영 QA','v6 적용 후 모달·CRUD·공개 반영 재검증','배너·팝업·게시판·공연·대관·계정 경로별 증거와 정리 확인'),
 ('3. 계정 정리','QA 전용 비활성 계정 잔존 확인','QA 계정만 삭제하고 실제 고객 계정·권한 보존'),
 ('4. 정책 확인','임시비밀번호 전환 및 서버 분리 대체통제','원문 대비 변경/대체 방식의 고객·보안 담당자 수용 범위 확인'),
 ('5. 운영 절차','기록 보관·백업·정기 점검 및 기관 IP 검증','책임자·주기·보관 기준과 허용/비허용 접속 결과 정리'),
 ('6. 문서 확정','실제 완료 결과로 본 문서 갱신','개선 예정 표시 해소 또는 승인된 잔여 항목 명시')
],[70,160,CW-230]))
h('배포와 유지관리')
para('v6 후속 수정은 웹 파일만이며 SQL·환경설정 재실행이나 계정 초기화를 요구하지 않습니다. 기존 운영 데이터와 업로드를 보존하고, 교체 파일 백업 후 경로 그대로 덮어쓰는 방식입니다. 로그 UI 추가 개발은 별도 수정본과 검증 결과로 관리합니다.')
para('SSL 설치 이후의 HTTP 허용 예외와 기존 /admin 경로 예외는 실제 운영 환경설정을 대조해 최종 차단 여부를 확인해야 합니다. 기록 보관, 사무실 허용 IP 유지, 비밀번호 재발급 전달 절차는 운영 담당자와 함께 관리합니다.')
h('검토 근거')
para('진단 PDF 18건, NPG 03 마스킹 가이드, 점검 결과_0417.xlsx, 기술검토_블루스퀘어.pdf. 구현 근거: v6 배포 manifest와 소스, QA_최종수정_v6_20261110.md, 실제 실서버 검수 기록 및 최종 자동검증 로그. 본문에는 운영 비밀값과 개인정보 원문을 포함하지 않았습니다.')
class NumberedCanvas(canvas.Canvas):
 def __init__(self,*args,**kwargs):super().__init__(*args,**kwargs);self._states=[]
 def showPage(self):self._states.append(dict(self.__dict__));self._startPage()
 def save(self):
  total=len(self._states)
  for state in self._states:
   self.__dict__.update(state);self.setStrokeColor(colors.HexColor('#DAE2EC'));self.line(44,43,W-44,43);self.setFont('Malgun',7.6);self.setFillColor(GRAY);self.drawString(44,29,'NINEONELABS  |  블루스퀘어  |  고객 검토용 초안');self.drawRightString(W-44,29,f'{self._pageNumber} / {total}');super().showPage()
  super().save()
def header(c,doc):
 c.setFillColor(TEAL);c.rect(0,H-12,W,12,fill=1,stroke=0);c.setFont('Malgun',7.7);c.setFillColor(GRAY);c.drawRightString(W-44,H-33,'2026.11.10  |  v6 기준')
p=OUT/'블루스퀘어_보안개선_개발보고서_고객검토초안_20261110.pdf'
doc=SimpleDocTemplate(str(p),pagesize=A4,leftMargin=44,rightMargin=44,topMargin=54,bottomMargin=56,title='블루스퀘어 관리자 보안 개선 개발 보고서 - 고객 검토용 초안',author='나인원랩스')
doc.build(story,onFirstPage=header,onLaterPages=header,canvasmaker=NumberedCanvas)
print(p)
