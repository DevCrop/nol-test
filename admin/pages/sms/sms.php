<?
include_once $_SERVER[DOCUMENT_ROOT]."/inc/lib/base.class.php";

$depthnum = 4;
$pagenum = 1;



include_once $_SERVER[DOCUMENT_ROOT]."/admin/inc/admin.title.php";
include_once $_SERVER[DOCUMENT_ROOT]."/admin/inc/admin.css.php";
include_once $_SERVER[DOCUMENT_ROOT]."/admin/inc/admin.js.php";
?>

</head>

<body>
	<div class="no_wrap">
		<!-- 상단 영역 -->
		<!-- BEGIN : HEADER -->
		<?
		include_once $_SERVER[DOCUMENT_ROOT]."/admin/inc/admin.header.php";
		?>
		<!-- END : HEADER -->
		<!-- 네비 영역 -->
		<div class="no_header_navi">
			<div class="no_hn_left">
				<span>문자 관리</span>
			</div>
			<div class="no_hn_right">
				<span class="">문자 관리</span>
				<div class="no_hn_navi">
					<span>문자 관리</span>
					<span  class="icon-right-open-mini">문자 발송</span>
				</div>
			</div>
		</div>
		<!-- 센터 영역 -->
		<div class="no_center">
			<!-- 사이드 메뉴 영역 -->
			<!-- BEGIN : LNB -->
			<?
				include_once $_SERVER[DOCUMENT_ROOT]."/admin/inc/admin.lnb.sms.php";
			?>
			<!-- END : LNB -->
			<!-- 컨텐츠 영역 -->
			<div class="no_content">
				<div class="no_content_div">
					<div class="no_sms_left">
						<div class="no_sms_left_top">
							<div class="no_slt_left">
								<span>휴대폰 번호 입력</span>
								<textarea name="" rows="10" cols="30" placeholder="번호를 입력해주세요."></textarea>
								<input type="button" value="+받는 사람 추가">
							</div>
							<span class="icon-right-open"></span>
							<div class="no_slt_right">
								<span>받는사람 총 <strong>0</strong>명</span>
								<span></span>
								<input type="button" value="- 선택 삭제하기">
							</div>
						</div>
						<div class="no_slt_bottom">
							<span class="icon-attention-circled">휴대폰 번호를 Enter 단위로 입력하세요.</span>
							<span class="icon-attention-circled">복사한 번호를 붙여넣어서 편리하게 추가할 수 있습니다.</span>
						</div>
						<div class="no_sms_left_cen">
							<div class="no_slc_top">
								<div class="no_slc_top_pos">
									<input type="checkbox" name="name_1" id="input1">
									<label for="input1">예약한 시간에 전송하기</label>									
								</div>					
							</div>
							<div class="no_slc_bot_pos">
								<div class="no_slc_bot">
									<div class="no_slc_bot_date">
										<span>날짜</span>
										<span class="no_slc_bot_box">
											<select name="year">
												<option value="1">-</option>
												<option value="2">2021</option>
												<option value="3">2022</option>
											</select>년</span>
										<span class="no_slc_bot_box">
											<select name="month">
												<option value="1">-</option>
												<option value="2">1</option>
												<option value="3">2</option>
												<option value="4">3</option>
												<option value="5">4</option>
												<option value="6">5</option>
												<option value="7">6</option>
												<option value="8">7</option>
												<option value="9">8</option>
												<option value="10">9</option>
												<option value="10">10</option>
												<option value="11">11</option>
												<option value="12">12</option>
											</select>월</span>
										<span class="no_slc_bot_box">
											<select name="date">
												<option value="1">-</option>
												<option value="2">1</option>
												<option value="3">2</option>
												<option value="4">3</option>
												<option value="5">4</option>
												<option value="6">5</option>
												<option value="7">6</option>
												<option value="8">7</option>
												<option value="9">8</option>
												<option value="10">9</option>
												<option value="10">10</option>
												<option value="11">11</option>
												<option value="12">12</option>
												<option value="13">13</option>
												<option value="14">14</option>
												<option value="15">15</option>
												<option value="16">16</option>
												<option value="17">17</option>
												<option value="18">18</option>
												<option value="19">19</option>
												<option value="20">10</option>
												<option value="21">21</option>
												<option value="22">22</option>
												<option value="23">23</option>
												<option value="24">24</option>
												<option value="25">25</option>
												<option value="26">26</option>
												<option value="27">27</option>
												<option value="28">28</option>
												<option value="29">29</option>
												<option value="30">30</option>
												<option value="31">31</option>
											</select>일
										</span>
									</div>
									<div class="no_slc_bot_time">
										<span>시간</span>
										<span class="no_slc_bot_box">
											<select name="time">
												<option value="1">00</option>
												<option value="2">01</option>
												<option value="3">02</option>
												<option value="4">03</option>
												<option value="5">04</option>
												<option value="6">05</option>
												<option value="7">06</option>
												<option value="8">07</option>
												<option value="9">08</option>
												<option value="10">09</option>
												<option value="11">10</option>
												<option value="12">11</option>
												<option value="13">12</option>
												<option value="14">13</option>
												<option value="15">14</option>
												<option value="16">15</option>
												<option value="17">16</option>
												<option value="18">17</option>
												<option value="19">18</option>
												<option value="20">19</option>
												<option value="21">20</option>
												<option value="22">21</option>
												<option value="23">22</option>
												<option value="24">23</option>
												<option value="25">24</option>
											</select>시
										</span>
										<span class="no_slc_bot_box">
											<select name="min">
												<option value="1">-</option>
												<option value="2">01</option>
												<option value="3">02</option>
												<option value="3">03</option>
												<option value="3">04</option>
												<option value="3">05</option>
												<option value="3">06</option>
												<option value="3">07</option>
												<option value="3">08</option>
												<option value="3">09</option>
												<option value="3">10</option>
												<option value="3">11</option>
												<option value="3">12</option>
												<option value="3">13</option>
												<option value="3">14</option>
												<option value="3">15</option>
												<option value="3">16</option>
												<option value="3">17</option>
												<option value="3">18</option>
												<option value="3">19</option>
												<option value="3">20</option>
												<option value="3">21</option>
												<option value="3">22</option>
												<option value="3">23</option>
												<option value="3">24</option>
												<option value="3">25</option>
												<option value="3">26</option>
												<option value="3">27</option>
												<option value="3">28</option>
												<option value="3">29</option>
												<option value="3">30</option>
												<option value="3">31</option>
												<option value="3">32</option>
												<option value="3">33</option>
												<option value="3">34</option>
												<option value="3">35</option>
												<option value="3">36</option>
												<option value="3">37</option>
												<option value="3">38</option>
												<option value="3">39</option>
												<option value="3">40</option>
												<option value="3">41</option>
												<option value="3">42</option>
												<option value="3">43</option>
												<option value="3">44</option>
												<option value="3">45</option>
												<option value="3">46</option>
												<option value="3">47</option>
												<option value="3">48</option>
												<option value="3">49</option>
												<option value="3">50</option>
												<option value="3">51</option>
												<option value="3">53</option>
												<option value="3">54</option>
												<option value="3">55</option>
												<option value="3">56</option>
												<option value="3">57</option>
												<option value="3">58</option>
												<option value="3">59</option>
											</select>분
										</span>
									</div>
								</div>
							</div>
						</div>
						<div class="no_sms_left_bot">
							<span>발신번호 
								<strong>070-4453-7428</strong>
							</span>
							<a href="#none">문자 전송하기</a>
						</div>
						<div class="no_sms_footer">
							<span class="icon-attention-circled">문자메세지의 제목은 <strong>LMS, MMS</strong>의 경우에만 발송됩니다.</span>
							<span class="icon-attention-circled">발신번호는 <u>환경설정 > 기본설정</u>에서 수정 가능하며, 발신번호 인증이 완료된 번호만 사용 가능합니다. </span>
							<span class="icon-attention-circled">발신번호는 인증은 나인원랩스로 문의해주시기 바랍니다.</span>
						</div>
					</div>
					<div class="no_sms_right">
						<div class="no_sr_top">
							<input type="text" placeholder="문자메세지 제목 입력">
							<textarea name="" rows="10" cols="30" placeholder="문자 내용을 입력해주세요."  ></textarea>
						</div>
						<div class="no_sr_bot">
						<span><span class="no_sr_bot_num">0</span>byte SMS</span>
							<label class="icon-camera" for="file"><label>
							<input type="file" name="file" id="file" style="display:none">
						</div>							
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- 상단 영역 -->
	<!-- BEGIN : HEADER -->
	<?
	include_once $_SERVER[DOCUMENT_ROOT]."/admin/inc/admin.footer.php";
	?>
	<!-- END : HEADER -->
</body>