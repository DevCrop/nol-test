<div class="no-relative__cnt-box mt80" <?=$aos_middle?>>
		<div class="no-relative__cnt">
			<div class="no-relative__txt-overview mb80">
			<ul <?=$aos_middle?>>
				<li>
				  <p>회사명</p>
				  <p>(주)대흥아스콘개발</p>
				</li>
				<li>
				  <p>대표자</p>
				  <p>김광현</p>
				</li>
				<li>
				  <p>주소</p>
				  <p>충북 충주시 중앙탑면 용전리 329-9</p>
				</li>
				<li>
				  <p>사업명</p>
				  <p>제조업아스콘</p>
				</li>
				<li>
				  <p>전화번호</p>
				  <p>043-841-0600</p>
				</li>
				<li>
				  <p>팩스번호</p>
				  <p>043-855-6609</p>
				</li>
				<li>
				  <p>이메일</p>
				  <p>dhbulid@naver.com</p>
				</li>
			  </ul>
			</div>
		<div class="no-relative__txt-overview">
			<ul>
				<li>
				  <p>생산시설</p>
				  <p>아스콘 배처플랜트 2기 (150 TON/HR)</p>
				</li>
				<li>
				  <p>생산능력</p>
				  <p>3,000 TON/DAY</p>
				</li>
				<li>
				  <p>운반장비</p>
				  <p>덤프트럭 30대</p>
				</li>
				<li>
				  <p>A/P 저장능력</p>
				  <p>300,000L</p>
				</li>
				<li>
				  <p>A/P 공급처</p>
				  <p>(주)GS칼텍스, (주)S-oil</p>
				</li>
			  </ul>
			</div>
		</div>
		<div class="no-relative__txt-card" <?=$aos_middle?>>
			<ul>
				<?
				foreach($arrResultSet as $k=>$v){
				$title = iconv_substr($v[title], 0, 100, "utf-8");
				$contents = strip_tags($v[contents]);
				$contents = iconv_substr($contents, 0, 500, "utf-8");
				$link = "./board.view.php?board_no=$board_no&no=$v[no]&searchKeyword=".base64_encode($searchKeyword)."&searchColumn=".base64_encode($searchColumn)."&page=$page";

				$imgSrc = "";
				if($v[thumb_image])
					$imgSrc = $UPLOAD_WDIR_BOARD."/".$v[thumb_image];
				else{
					$imgSrc = getImageTag($v[contents], "src");
					$imgSrc = $imgSrc[0];
				} 

				$link_image = $imgSrc;

				$imgData = 'data-pswp-width="1600" data-pswp-height="1024" class="my-image"';
				
				if($v['direct_url']){
					$link_image = $v['direct_url'];
					$imgData = '';
				} 
			?>
			  <li>
				<!--logo-->
				<div class="no-relative__logo">
				  <img
					src="/resource/images/dh_bulid_logo.png"
					alt="디에이치건설(주) 로고"
				  />
				</div>
				<!--공사명-->
				<div class="no-relative__name"><?=$v['extra1']?></div>
				<!--발주처-->
				<div class="no-relative__client"><?=$v['extra2']?></div>
				<!--공사금액-->
				<div class="no-relative__cost"><?=$v['extra3']?></div>
				<!--년도-->
				<div class="no-relative__year"><?=$v['extra4']?></div>
			  </li>
				 <?
					$rnumber--;
					}
				?>
			</ul>
		</div>
	</div>