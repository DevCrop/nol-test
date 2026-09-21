<?
	include_once "../../../inc/lib/base.class.php";


	include_once "../../inc/admin.title.php";
	include_once "../../inc/admin.css.php";
	include_once "../../inc/admin.js.php";
?>
</head>

<body>
	<div class="no_wrap">
		<!-- 상단 영역 -->
		<!-- BEGIN : HEADER -->
		<?
		include_once "../../inc/admin.header.php";
		?>
		<!-- END : HEADER -->
		<!-- 네비 영역 -->
		<div class="no_header_navi">
			<div class="no_hn_left">
				<span>설정</span>
			</div>
			<div class="no_hn_right">
				<span class="">브라우저 최적화</span>
				<div class="no_hn_navi">
					<span>설정</span>
					<span class="icon-right-open-mini">브라우저 최적화</span>
				</div>
			</div>
		</div>
		<!-- 센터 영역 -->
		<div class="no_center">
			<!-- 사이드 메뉴 영역 -->
			<!-- BEGIN : LNB -->
			<?
				include_once "../../inc/admin.lnb.setting.php";
			?>
			<!-- END : LNB -->
			<!-- 컨텐츠 영역 -->

			<div class="no_content">
				<div class="no_content_div">
					<div class="no_info_txt">
						<span>
							나인원랩스의 솔루션은 최신 브라우저에서 가장 최상의 성능을 발휘하도록 개발되었습니다.
							<br>
							11버전 이하의 인터넷 익스플로러는 곧 기술 지원이 종료되니 구글 크롬 또는 마이크로소프트 엣지와 같은
							최신 버전의 브라우저를 사용해주시기 바랍니다.
						</span>
					</div>
					<div class="no_info_link">
						<a href="http://www.google.co.kr/chrome/" title="구글 크롬 다운로드">
							<div class="no_info_link_div">
								<img src="../../resource/images/chrome_logo.png" alt="구글 크롬 아이콘"/>
								<span	class="no_info_link_div_btn">다운받기</span>
							</div>
						</a>
						<a href="http://www.microsoft.com/ko-kr/edge" title="마이크로소프트 엣지 다운로드">
							<div class="no_info_link_div">
								<img src="../../resource/images/edge_logo.png" alt="마이크로소프트 엣지 아이콘"/>
								<span class="no_info_link_div_btn">다운받기</span>
							</div>
						</a>
					</div>
				</div>			
			</div>
			</form>
		</div>
	</div>
	<!-- 하단 영역 -->
	<script type="text/javascript" src="./js/setting.process.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"></script>
	<!-- BEGIN : FOOTER -->
	<?
		include_once "../../inc/admin.footer.php";
	?>
	<!-- END : FOOTER -->
</body>