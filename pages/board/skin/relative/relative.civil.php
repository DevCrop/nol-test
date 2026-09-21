<div class="no-relative__cnt-box" <?=$aos_middle?>>
	<div class="no-relative__cnt">
		<div class="no-relative__txt-overview mb80">
		  <ul <?=$aos_middle?>>
			<li>
			  <p>회사명</p>
			  <p>(주)대흥토건</p>
			</li>
			<li>
			  <p>대표자</p>
			  <p>김광현</p>
			</li>
			<li>
			  <p>주소</p>
			  <p>충북 충주시 중앙탑면 감노로 2230</p>
			</li>
			<li>
			  <p>전화번호</p>
			  <p>043-841-0500</p>
			</li>
			<li>
			  <p>팩스번호</p>
			  <p>043-855-9984</p>
			</li>
			<li>
			  <p>이메일</p>
			  <p>dhbulid@naver.com</p>
			</li>
			<li>
			  <p>회사설립년도</p>
			  <p>1996년 07월 05일</p>
			</li>
			<li>
			  <p>자본금</p>
			  <p>이십육억일천만(2,610,000,000원)</p>
			</li>
		  </ul>
		</div>
		<div class="no-relative__txt-goal" >
		  <div class="table">
			<table class="tg">
			  <thead>
				<tr>
				  <th class="tg-0pky">보유면허</th>
				  <th class="tg-0lax">시공능력평가액(2022)</th>
				  <th class="tg-0lax">3년간 실적(2020 - 2022)</th>
				  <th class="tg-0lax">5년간 실적(2018 - 2022)</th>
				</tr>
			  </thead>
			  <tbody>
				<tr>
				  <td class="tg-0lax">토공사업</td>
				  <td class="tg-0lax">59,359,000</td>
				  <td class="tg-0lax">117,474,000</td>
				  <td class="tg-0lax">220,238,000</td>
				</tr>
				<tr>
				  <td class="tg-0lax">철근, 콘크리트 공사업</td>
				  <td class="tg-0lax">34,413,000</td>
				  <td class="tg-0lax">34,842,000</td>
				  <td class="tg-0lax">62,169,000</td>
				</tr>
				<tr>
				  <td class="tg-0lax">포장공사업</td>
				  <td class="tg-0lax">11,304,000</td>
				  <td class="tg-0lax">1,794,000</td>
				  <td class="tg-0lax">3,551,000</td>
				</tr>
				<tr>
				  <td class="tg-0lax">상·하수도설비공사업</td>
				  <td class="tg-0lax">11,788,000</td>
				  <td class="tg-0lax">1,145,000</td>
				  <td class="tg-0lax">6,093,000</td>
				</tr>
				<tr>
				  <td class="tg-0lax">금속구조물 창호공사업</td>
				  <td class="tg-0lax">10,684,000</td>
				  <td class="tg-0lax">77,000</td>
				  <td class="tg-0lax">77,000</td>
				</tr>
				<tr>
				  <td class="tg-0lax">조경식재공사업</td>
				  <td class="tg-0lax">4,604,000</td>
				  <td class="tg-0lax">819,000</td>
				  <td class="tg-0lax">1,185,000</td>
				</tr>
				<tr>
				  <td class="tg-0lax">조경시설물설치공사업</td>
				  <td class="tg-0lax">4,077,000</td>
				  <td class="tg-0lax">259,000</td>
				  <td class="tg-0lax">259,000</td>
				</tr>
				<tr>
				  <td class="tg-0lax">비계·구조물해체공사업</td>
				  <td class="tg-0lax">10,798,000</td>
				  <td class="tg-0lax">2,189,000</td>
				  <td class="tg-0lax">2,189,000</td>
				</tr>
				<tr>
				  <td class="tg-0lax">보링·그라우팅공사업</td>
				  <td class="tg-0lax">10,345,000</td>
				  <td class="tg-0lax">336,000</td>
				  <td class="tg-0lax">336,000</td>
				</tr>
				<tr>
				  <td class="tg-0lax">습식방수공사업</td>
				  <td class="tg-0lax">11,008,000</td>
				  <td class="tg-0lax">209,000</td>
				  <td class="tg-0lax">209,000</td>
				</tr>
				<tr>
				  <td class="tg-0lax">토목공사업</td>
				  <td class="tg-0lax">35,382,000</td>
				  <td class="tg-0lax">27,080,000</td>
				  <td class="tg-0lax">28,572,000</td>
				</tr>
				<tr>
				  <td class="tg-0lax">건축공사업</td>
				  <td class="tg-0lax">20,314,000</td>
				  <td class="tg-0lax">2,551,000</td>
				  <td class="tg-0lax">2,551,000</td>
				</tr>
				<tr>
				  <td class="tg-0lax">토건공사업</td>
				  <td class="tg-0lax">35,382,000</td>
				  <td class="tg-0lax">29,631,000</td>
				  <td class="tg-0lax">31,123,000</td>
				</tr>
			  </tbody>
			</table>
		  </div>
		  <!--주석-->
		  <div class="no-org__count">
			<p>단위 (천원)</p>
		  </div>
		</div>
	</div>
	<div class="no-relative__txt-card">
		<ul>
			<?
			foreach($arrResultSet as $k=>$v){
			$title = iconv_substr($v[title], 0, 100, "utf-8");
			$contents = strip_tags($v[contents]);
			$contents = iconv_substr($contents, 0, 500, "utf-8");
			$link = "../../board.view.php?board_no=$board_no&no=$v[no]&searchKeyword=".base64_encode($searchKeyword)."&searchColumn=".base64_encode($searchColumn)."&page=$page";

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
				src="/resource/images/logo_civil.svg"
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