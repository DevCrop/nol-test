<!DOCTYPE html>
<html lang="ko">
<?
	$depthnum = 10;
	$pagenum = 1;

	include_once "../../../inc/lib/base.class.php";
	include_once '../../../inc/lib/core/autoload.php';
	
	include_once "../../inc/admin.title.php";
	include_once "../../inc/admin.css.php";
	include_once "../../inc/admin.js.php";
?>

</head>

<body>
    <div class="no-wrap">
        <!-- Header -->
        <?
        include_once "../../inc/admin.header.php";
        ?>

        <!-- Main -->
        <main class="no-app no-container">
            <!-- Drawer -->
            <?
                include_once "../../inc/admin.drawer.php";
            ?>

            <form id="frm" name="frm" method="post" enctype="multipart/form-data">
			 <input type="hidden" name="map_id" id="map_id">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                            <h1 class="no-page-title">지도 관리</h1>
                            <div class="no-breadcrumb-container">
                                <ul class="no-breadcrumb-list">
                                <li class="no-breadcrumb-item">
                                    <span>지도보드</span>
                                </li>
                                <li class="no-breadcrumb-item">
                                    <span>지도 관리</span>
                                </li>
                                </ul>
                            </div>
                            </div>
                            <!-- page indicator -->
                        </div>
                    </div>


                    <!-- card-title -->
                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">지도 등록</h2>
                            </div>
                            <div
                                class="no-card-body no-admin-column no-admin-column--detail"
                            >

								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="title">지도 이름</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="title"
                                            id="title"
                                            class="no-input--detail"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="board_no"> 지도 선택 </label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <select name="map_type" id="map_type" > 
                                            <option value="">지도 선택</option>
                                            <option value="address" selected>주소</option>
											<option value="keyword">키워드</option>
											<option value="coord">좌표</option>
                                        </select>
                                        <span class="no-admin-info">
                                            <i class="bx bxs-info-circle"></i>
                                            지도형태를 선택해주세요.
                                        </span>
                                    </div>
                                </div>
                                <!-- admin-block -->

								

                                <div class="no-admin-block no-admin-pos">
                                    <h3 class="no-admin-title">
                                        <label for="map_value">검색 값</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="map_value"
                                            id="map_value"
                                            class="no-input--detail"
                                            placeholder="지도이름을 입력해주세요."
                                        />
                                    </div>
									<button type="button" class="no-btn no-btn--big no-btn--main" id="search-btn">검색</button>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="coord_x">위도(x)</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="coord_x"
                                            id="coord_x"
                                            class="no-input--detail"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                               <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="coord_y">경도(y)</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="coord_y"
                                            id="coord_y"
                                            class="no-input--detail"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <strong>지도보기</strong>
                                    </h3>
                                    <div class="no-admin-content">
										<div id="map"></div>
                                    </div>
                                </div>
                                <!-- admin-block -->

								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="address">주소</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="address"
                                            id="address"
                                            placeholder=""
                                            class="no-input--detail"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->
								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="tel">전화</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="tel"
                                            name="tel"
                                            id="tel"
                                            placeholder=""
                                            class="no-input--detail"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="fax">팩스</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="fax"
                                            id="fax"
                                            placeholder=""
                                            class="no-input--detail"
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->


                                <div class="no-items-center center">
                                    <a
                                        href="./map.list.php"
                                        class="no-btn no-btn--big no-btn--normal"
                                    >
                                        목록
                                    </a>
                                    <button type="button" id="submit-btn"
                                        class="no-btn no-btn--big no-btn--main"
                                    >
                                        저장
                                    </a>
                                </div>
                            </div>
                            <!-- card-body -->
                        </div>
                    </div>
                </section>
            </form>
        </main>

        
        <?
            include_once "../../inc/admin.footer.php";
        ?>
        
    </div>

	<script type="text/javascript"
    src="//dapi.kakao.com/v2/maps/sdk.js?appkey=<?=KAKAO_JAVASCRIPT_KEY?>&libraries=services"></script>
	<script type="module">
		import KakaoMap from '/resource/js/map/KakaoMap.js';
		const map = new KakaoMap('map');
		map.view();
	</script>

	<script>
		const form = document.querySelector('#frm');
		const submitBtn = document.querySelector('#submit-btn');
		submitBtn.addEventListener('click', async function(){
			if(!$('#title').val()) {
				alert('지도이름을 입력해주세요.');
				$('#title').focus();
				return;
			}

			if(!$('#map_type').val()) {
				alert('지도형태 선택해주세요.');
				$('#map_type').focus();
				return;
			}


			if(!$('#map_value').val()) {
				alert('값을 입력해주세요.');
				$('#map_value').focus();
				return;
			}

			if(!$('#address').val()) {
				alert('주소를 입력해주세요.');
				$('#address').focus();
				return;
			}



			const url = './api.php';
			const fd = new FormData(form);

			const response = await fetch(url, {
				method: 'POST', 
				body: fd
			});

			const result = await response.json();


			if(!result.success) {
				alert(result.message); 
				return; 
			}

			alert(result.message);
			location.href = './map.list.php';
		});
	</script>
</body>
</html>
