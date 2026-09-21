<div class="no-relative__cnt-box" <?=$aos_middle?>>
		<div class="no-relative__cnt">
			<div class="no-relative__txt-overview mb80">
			  <ul <?=$aos_middle?>>
				<li>
				  <p>회사명</p>
				  <p>디에이치건설(주)</p>
				</li>
				<li>
				  <p>대표자</p>
				  <p>김광현</p>
				</li>
				<li>
				  <p>주소</p>
				  <p>경복 예천군 예천읍 중앙로 20</p>
				</li>
				<li>
				  <p>전화번호</p>
				  <p>054-655-9400</p>
				</li>
				<li>
				  <p>팩스번호</p>
				  <p>054-655-9401</p>
				</li>
				<li>
				  <p>이메일</p>
				  <p>dhbulid@naver.com</p>
				</li>
				<li>
				  <p>회사설립년도</p>
				  <p>1994년 10월 1일</p>
				</li>
				<li>
				  <p>자본금</p>
				  <p>일십사억구백만원(1,409,000,000원)</p>
				</li>
			  </ul>
			</div>
			<div class="no-relative__txt-goal">
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
					  <td class="tg-0lax">토목공사업</td>
					  <td class="tg-0lax">10,885,000</td>
					  <td class="tg-0lax">7,430,000</td>
					  <td class="tg-0lax">8,721,000</td>
					</tr>
					<tr>
					  <td class="tg-0lax">건축공사업</td>
					  <td class="tg-0lax">16,631,000</td>
					  <td class="tg-0lax">22,418,000</td>
					  <td class="tg-0lax">37,287,000</td>
					</tr>
					<tr>
					  <td class="tg-0lax">토목건축공사업</td>
					  <td class="tg-0lax">18,439,000</td>
					  <td class="tg-0lax">29,848,000</td>
					  <td class="tg-0lax">46,008,000</td>
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
					src="/resource/images/logo_con.svg"
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