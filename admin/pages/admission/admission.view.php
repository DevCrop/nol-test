<!DOCTYPE html>
<html lang="ko">
<?
include_once $_SERVER[DOCUMENT_ROOT]."/inc/lib/base.class.php";

$depthnum = 9;
$pagenum = 1;



$no	= $_REQUEST[no];

$query = "  select
						a.no
						,a.sitekey
						,a.session_id
						,a.kind
						,a.campus
						,a.grade                                 
						,a.photo
						,a.name_korean
						,a.name_english
						,a.preferred_english_name
						,a.date_of_birth
						,a.country_of_birth
						,a.country_of_citizenship1
						,a.country_of_citizenship2
						,a.gender
						,a.address_in_korea_zipcode
						,a.address_in_korea_addr1
						,a.address_in_korea_addr2
						,a.school_bus_request
						,a.which_kindergarten_attend1
						,a.which_kindergarten_attend2
						,a.which_kindergarten_attend3
						,a.which_kindergarten_attend4
						,a.which_kindergarten_attend5
						,a.which_kindergarten_attend6
						,a.which_kindergarten_attend7
						,a.which_kindergarten_attend8
						,a.which_kindergarten_attend9
						,a.which_kindergarten_attend10
						,a.g5_entrance_school
						,a.g5_entrance_grade
						,a.experience_living_abroad
						,a.experience_living_abroad_year
						,a.school_primarily
						,a.english_speaking_fluency
						,a.which_type_of_high_school
						,a.education_background_app_attened1
						,a.education_background_date_grade_enrolled1
						,a.education_background_date_grade_left1
						,a.education_background_date_city_country1
						,a.education_background_app_attened2
						,a.education_background_date_grade_enrolled2
						,a.education_background_date_grade_left2
						,a.education_background_date_city_country2
						,a.education_background_app_attened3
						,a.education_background_date_grade_enrolled3
						,a.education_background_date_grade_left3
						,a.education_background_date_city_country3
						,a.education_background_app_attened4
						,a.education_background_date_grade_enrolled4
						,a.education_background_date_grade_left4
						,a.education_background_date_city_country4
						,a.condition_repeated_grade
						,a.condition_skipped_grade
						,a.condition_participated_esl
						,a.condition_suspended_expelled
						,a.condition_diagnosed_learning
						,a.condition_repeated_grade_why
						,a.condition_skipped_grade_why
						,a.condition_participated_esl_instruction
						,a.condition_suspended_expelled_resolved
						,a.special_reading_disability
						,a.special_writing_disability
						,a.special_mathematics_disability
						,a.special_attention_deficit
						,a.special_other
						,a.marital_status_of_parents
						,a.parent1_relationship                                 
						,a.parent1_name
						,a.parent1_citizenship
						,a.parent1_occupation
						,a.parent1_tel
						,a.parent1_mobile_phone
						,a.parent1_email_address
						,a.parent1_receiving_notice
						,a.parent2_relationship                                 
						,a.parent2_name
						,a.parent2_citizenship
						,a.parent2_occupation
						,a.parent2_tel
						,a.parent2_mobile_phone
						,a.parent2_email_address
						,a.parent2_receiving_notice
						,a.cash_receipt
						,a.cash_receipt_code
						,a.siblings1_name
						,a.siblings1_gender
						,a.siblings1_grade
						,a.siblings1_date_of_birth
						,a.siblings1_school_attending
						,a.siblings2_name
						,a.siblings2_gender
						,a.siblings2_grade
						,a.siblings2_date_of_birth
						,a.siblings2_school_attending
						,a.siblings3_name
						,a.siblings3_gender
						,a.siblings3_grade
						,a.siblings3_date_of_birth
						,a.siblings3_school_attending
						,a.additional_comments
						,a.portfolio1                                 
						,a.portfolio2
						,a.portfolio3
						,a.portfolio4
						,a.about_sa
						,a.desired_starting_date
						,a.remote_ip
						,a.is_complete
						,a.parent_signature
						,a.regdate
						,b.name as campus_name
				 from nb_admission a
				left join nb_campus b on a.campus = b.no
				where a.no = $no  ";

$result=mysql_query($query);
$data=mysql_fetch_array($result);
if(!$data){
	error("정보를 찾을 수 없습니다");
}



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

            <!-- Contents -->
            <?
                $query = "select no, title, skin, sort_no  from nb_board_manage order by no asc";
                $result_2nd =mysql_query($query);
                $arrBoardList = array();
                while($row = mysql_fetch_assoc($result_2nd)) {
                    $arrBoardList[] = $row;
                }
            ?>
            <form id="frm" name="frm" method="post" enctype="multipart/form-data">
            <input type="hidden" id="mode" name="mode" value="">
            <input type="hidden" id="no" name="no" value="<?=$data[no]?>">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title">Admission 목록</h1>

                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item">
                                            <span>Admission</span>
                                        </li>
                                        <li class="no-breadcrumb-item">
                                            <span>Admission 목록</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- page indicator -->
                        </div>
                    </div>
                    
                    <div class="no-admin-tab">
                        <ul class="no-admin-tab__list">
                            <li class="no-admin-tab__item">
                                <button type="button" class="no-btn">Student Information</button>
                            </li>
                            <li class="no-admin-tab__item">
                                <button type="button" class="no-btn">Family Information</button>
                            </li>
                            <li class="no-admin-tab__item">
                                <button type="button" class="no-btn">Additional Comments</button>
                            </li>
                        </ul>
                    </div>

					<!-- Student Information ===================================================================== -->
                    <div class="no-toolbar-container no-admin-tab__block">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">Student Information</h2>
                            </div>
                            <div
                                class="no-card-body no-admin-column no-admin-column--detail"
                            >

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="campus">Campus</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="campus"
                                            id="campus"
                                            class="no-input--detail"
                                            value="<?=$data[campus_name]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->
                                
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="grade">Grade Applying for</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="grade"
                                            id="grade"
                                            class="no-input--detail"
                                            value="<?=$data[grade]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="photo">Photo</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-banner-image">
                                            <img
                                                src="<?=$UPLOAD_WDIR_ADMISSION?>/<?=$data[photo]?>"
                                                alt="<?=$data[photo]?>"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="korean_name">Korean Name</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="korean_name"
                                            id="korean_name"
                                            class="no-input--detail"
                                            value="<?=$data[name_korean]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="english_name">English Name</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="english_name"
                                            id="english_name"
                                            class="no-input--detail"
                                            value="<?=$data[name_english]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="preferred_name">Preferred English Name</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="preferred_name"
                                            id="preferred_name"
                                            class="no-input--detail"
                                            value="<?=$data[preferred_english_name]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="date_of_birth">Date of Birth</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="date"
                                            name="date_of_birth"
                                            id="date_of_birth"
                                            class="no-input--detail"
                                            value="<?=$data[date_of_birth]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="country_of_birth">Country of Birth</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="country_of_birth"
                                            id="country_of_birth"
                                            class="no-input--detail"
                                            value="<?=$data[country_of_birth]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="country_of_citizenship1">Country of Citizenship1</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="country_of_citizenship1"
                                            id="country_of_citizenship1"
                                            class="no-input--detail"
                                            value="<?=$data[country_of_citizenship1]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->
								
								<div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="country_of_citizenship1">Country of Citizenship2</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="country_of_citizenship1"
                                            id="country_of_citizenship1"
                                            class="no-input--detail"
                                            value="<?=$data[country_of_citizenship2]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="gender">Gender</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="gender"
                                            id="gender"
                                            class="no-input--detail"
                                            value="<?=$data[gender]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <strong>Residential Address In Korea</strong>
                                    </h3>
                                    <div class="no-admin-content">
                                        <ul class="list">
                                            <li><?=$data[address_in_korea_zipcode]?></li>
                                            <li><?=$data[address_in_korea_addr1]?></li>
                                            <li><?=$data[address_in_korea_addr2]?></li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- admin-block -->
								
								<?
									if($data['campus'] != 3 || $data['campus'] != '3'){
								?>
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="school_bus_request">School Bus Request <br>(셔틀버스 신청 여부)</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="school_bus_request"
                                            id="school_bus_request"
                                            class="no-input--detail"
                                            value="<?=$data[school_bus_request]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->
								<? } ?>

								<?
									if($data['campus'] == 1){
								?>
								<!-- 시작 : Apgujeong 페이지만 나옵니다. ===================================== -->
								<? 
									for($i=1;$i<10;$i++){
										if($data['which_kindergarten_attend'.$i]){ 
								?>
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="which_kindergarten_attend<?=$i?>">졸업한/재학중인 유치원 <?=$i?></label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="which_kindergarten_attend<?=$i?>"
                                            id="which_kindergarten_attend<?=$i?>"
                                            class="no-input--detail"
                                            value="<?=$data['which_kindergarten_attend'.$i]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->
								<?
										}
									}
								?>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <strong for="gradated_school">
                                        K ~ G5 입학 경우, <br>
                                        재학중인 학교/학년
                                        </strong>
                                    </h3>
                                    <div class="no-admin-content">
                                        <ul class="list block">
                                            <li class="in-school">
                                                <div>
													<label for="graduated_school">학교</label>
													<input
														type="text"
														name="graduated_school"
														id="graduated_school"
														class="no-input--detail"
														value="<?=$data['g5_entrance_school']?>"
														readonly
													/>
                                                </div>
												<div>
													<label for="g5_entrance_grade">학년</label>
													<input
														type="text"
														name="g5_entrance_grade"
														id="g5_entrance_grade"
														class="no-input--detail"
														value="<?=$data['g5_entrance_grade']?>"
														readonly
													/>
												</div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="experience_living_abroad">
                                        Experience living abroad?
                                        </label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="experience_living_abroad"
                                            id="experience_living_abroad"
                                            class="no-input--detail"
                                            value="<?=$data['experience_living_abroad']?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="experience_living_abroad_year">
                                        How many years living abroad?
                                        </label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="experience_living_abroad_year"
                                            id="experience_living_abroad_year"
                                            class="no-input--detail"
                                            value="<?=$data['experience_living_abroad_year']?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->
                                
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="english_speaking_fluency">English Speaking Fluency</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="english_speaking_fluency"
                                            id="english_speaking_fluency"
                                            class="no-input--detail"
                                            value="<?=$data['english_speaking_fluency']?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->
								<!-- 끝 : Apgujeong 페이지만 나옵니다. ===================================== -->
							<?
								} else if($data['campus'] == 2) {
							?>

								<!-- 시작 : Daechi 페이지만 나옵니다. ===================================== -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <strong for="gradated_school">
                                        G1 ~ G8 입학 경우, <br>
                                        재학중인 학교/학년
                                        </strong>
                                    </h3>
                                    <div class="no-admin-content">
                                        <ul class="list block">
                                            <li>
                                                <label for="graduated_school">학교</label>
                                                <input
                                                    type="text"
                                                    name="graduated_school"
                                                    id="graduated_school"
                                                    class="no-input--detail"
                                                    value="<?=$data['g5_entrance_school']?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="g5_entrance_grade">학년</label>
                                                <input
                                                    type="text"
                                                    name="g5_entrance_grade"
                                                    id="g5_entrance_grade"
                                                    class="no-input--detail"
                                                    value="<?=$data['g5_entrance_grade']?>"
                                                    readonly
                                                />
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="experience_living_abroad">
                                        Experience living abroad?
                                        </label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="experience_living_abroad"
                                            id="experience_living_abroad"
                                            class="no-input--detail"
                                            value="<?=$data['experience_living_abroad']?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="experience_living_abroad_year">
                                        How many years living abroad?
                                        </label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="experience_living_abroad_year"
                                            id="experience_living_abroad_year"
                                            class="no-input--detail"
                                            value="<?=$data['experience_living_abroad_year']?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->
                                
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="english_speaking_fluency">English Speaking Fluency</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="english_speaking_fluency"
                                            id="english_speaking_fluency"
                                            class="no-input--detail"
                                            value="<?=$data['english_speaking_fluency']?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <strong>
                                        Which type of high school does the student want to apply for?
                                        </strong>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-textbox">
                                            <?=$data['which_type_of_high_school']?>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin-block -->
								<!-- 끝 : Daechi 페이지만 나옵니다. ===================================== -->
							<?
								}else if($data['campus'] == 3){
							?>
								<!-- 시작 : Daechi Division 페이지만 나옵니다. ===================================== -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="school_primarily">
                                        Experience to Attend the School Primarily Taught in English
                                        </label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="school_primarily"
                                            id="school_primarily"
                                            class="no-input--detail"
                                            value="<?=$data['school_primarily']?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->
                                
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="english_speaking_fluency">English Speaking Fluency</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="english_speaking_fluency"
                                            id="english_speaking_fluency"
                                            class="no-input--detail"
                                            value="<?=$data['english_speaking_fluency']?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <strong>
                                        Education Background
                                        </strong>
                                    </h3>
                                    <div class="no-admin-content">
                                        <ul class="list block border">
                                            <li>
                                                <label for="education_background_app_attened1">
                                                    Previous schools Applicant Attended
                                                </label>
                                                <input
                                                    type="text"
                                                    name="education_background_app_attened1"
                                                    id="education_background_app_attened1"
                                                    class="no-input--detail"
                                                    value="<?=$data['education_background_app_attened1']?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="education_background_date_grade_enrolled1">
                                                Date (mm/yy) & Grade Enrolled
                                                </label>
                                                <input
                                                    type="text"
                                                    name="education_background_date_grade_enrolled1"
                                                    id="education_background_date_grade_enrolled1"
                                                    class="no-input--detail"
                                                    value="<?=$data['education_background_date_grade_enrolled1']?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="education_background_date_grade_left1">
                                                Date (mm/yy) & Grade Left
                                                </label>
                                                <input
                                                    type="text"
                                                    name="education_background_date_grade_left1"
                                                    id="education_background_date_grade_left1"
                                                    class="no-input--detail"
                                                    value="<?=$data['education_background_date_grade_left1']?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="education_background_app_attened1">
                                                City / Country
                                                </label>
                                                <input
                                                    type="text"
                                                    name="education_background_date_city_country1"
                                                    id="education_background_date_city_country1"
                                                    class="no-input--detail"
                                                    value="<?=$data['education_background_date_city_country1']?>"
                                                    readonly
                                                />
                                            </li>
                                        </ul>
                                        <ul class="list block border">
                                            <li>
                                                <label for="education_background_app_attened2">
                                                    Previous schools Applicant Attended
                                                </label>
                                                <input
                                                    type="text"
                                                    name="education_background_app_attened2"
                                                    id="education_background_app_attened2"
                                                    class="no-input--detail"
                                                    value="<?=$data['education_background_app_attened2']?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="education_background_date_grade_enrolled2">
                                                Date (mm/yy) & Grade Enrolled
                                                </label>
                                                <input
                                                    type="text"
                                                    name="education_background_date_grade_enrolled2"
                                                    id="education_background_date_grade_enrolled2"
                                                    class="no-input--detail"
                                                    value="<?=$data['education_background_date_grade_enrolled2']?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="education_background_date_grade_left2">
                                                Date (mm/yy) & Grade Left
                                                </label>
                                                <input
                                                    type="text"
                                                    name="education_background_date_grade_left2"
                                                    id="education_background_date_grade_left2"
                                                    class="no-input--detail"
                                                    value="<?=$data['education_background_date_grade_left2']?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="education_background_date_city_country2">
                                                City / Country
                                                </label>
                                                <input
                                                    type="text"
                                                    name="education_background_date_city_country2"
                                                    id="education_background_date_city_country2"
                                                    class="no-input--detail"
                                                    value="<?=$data['education_background_date_city_country2']?>"
                                                    readonly
                                                />
                                            </li>
                                        </ul>
                                        <ul class="list block border">
                                            <li>
                                                <label for="education_background_app_attened3">
                                                    Previous schools Applicant Attended
                                                </label>
                                                <input
                                                    type="text"
                                                    name="education_background_app_attened3"
                                                    id="education_background_app_attened3"
                                                    class="no-input--detail"
                                                    value="<?=$data['education_background_app_attened3']?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="education_background_date_grade_enrolled3">
                                                Date (mm/yy) & Grade Enrolled
                                                </label>
                                                <input
                                                    type="text"
                                                    name="education_background_date_grade_enrolled3"
                                                    id="education_background_date_grade_enrolled3"
                                                    class="no-input--detail"
                                                    value="<?=$data['education_background_date_grade_enrolled3']?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="education_background_date_grade_left3">
                                                Date (mm/yy) & Grade Left
                                                </label>
                                                <input
                                                    type="text"
                                                    name="education_background_date_grade_left3"
                                                    id="education_background_date_grade_left3"
                                                    class="no-input--detail"
                                                    value="<?=$data['education_background_date_grade_left3']?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="education_background_date_city_country3">
                                                City / Country
                                                </label>
                                                <input
                                                    type="text"
                                                    name="education_background_date_city_country3"
                                                    id="education_background_date_city_country3"
                                                    class="no-input--detail"
                                                    value="<?=$data['education_background_date_city_country3']?>"
                                                    readonly
                                                />
                                            </li>
                                        </ul>
                                        <ul class="list block border">
                                            <li>
                                                <label for="education_background_app_attened4">
                                                    Previous schools Applicant Attended
                                                </label>
                                                <input
                                                    type="text"
                                                    name="education_background_app_attened4"
                                                    id="education_background_app_attened4"
                                                    class="no-input--detail"
                                                    value="<?=$data['education_background_app_attened4']?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="education_background_date_grade_enrolled4">
                                                Date (mm/yy) & Grade Enrolled
                                                </label>
                                                <input
                                                    type="text"
                                                    name="education_background_date_grade_enrolled4"
                                                    id="education_background_date_grade_enrolled4"
                                                    class="no-input--detail"
                                                    value="<?=$data['education_background_date_grade_enrolled4']?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="education_background_date_grade_left4">
                                                Date (mm/yy) & Grade Left
                                                </label>
                                                <input
                                                    type="text"
                                                    name="education_background_date_grade_left4"
                                                    id="education_background_date_grade_left4"
                                                    class="no-input--detail"
                                                    value="<?=$data['education_background_date_grade_left4']?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="education_background_date_city_country4">
                                                City / Country
                                                </label>
                                                <input
                                                    type="text"
                                                    name="education_background_date_city_country4"
                                                    id="education_background_date_city_country4"
                                                    class="no-input--detail"
                                                    value="<?=$data['education_background_date_city_country4']?>"
                                                    readonly
                                                />
                                            </li>
                                        </ul>
                                        
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="english_speaking_fluency">
                                        conditions apply to the candidate
                                        </label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <ul class="list block border no-check-list">
                                            <li>
                                                <label for="condition_repeated_grade" style="display: flex; align-items: center; gap: 1rem; pointer-events: none;">
												<?php $isConditionChecked = $data['condition_repeated_grade'] == 'Y' ? 'checked' : null?>
												<div class="no-checkbox-form" style="display: inline-block;">
													<input type="checkbox" class="no-input--detail" <?=$isConditionChecked?>>
													<span>
														<i class="bx bxs-check-square"></i>
													</span>
												</div>
                                                Repeated a Grade
                                                </label>
                                                <!-- condition description 1 -->
                                                <div class="no-textbox"><?=$data['condition_repeated_grade_why']?></div>
                                            </li>
                                            <li>
                                                <label for="condition_skipped_grade" style="display: flex; align-items: center; gap: 1rem; pointer-events: none;">
													<?php $isConditionChecked = $data['condition_skipped_grade'] == 'Y' ? 'checked' : null?>
													<div class="no-checkbox-form" style="display: inline-block;">
														<input type="checkbox" class="no-input--detail" <?=$isConditionChecked?>>
														<span>
															<i class="bx bxs-check-square"></i>
														</span>
													</div>
													Skipped a grade
                                                </label>
                                                <!-- condition description 2 -->
                                                <div class="no-textbox"><?=$data['condition_skipped_grade_why']?></div>
                                            </li>
                                            <li>
                                                <label for="condition_participated_esl" style="display: flex; align-items: center; gap: 1rem; pointer-events: none;">
												<?php $isConditionChecked = $data['condition_participated_esl'] == 'Y' ? 'checked' : null?>
												<div class="no-checkbox-form" style="display: inline-block;">
													<input type="checkbox" class="no-input--detail" <?=$isConditionChecked?>>
													<span>
														<i class="bx bxs-check-square"></i>
													</span>
												</div>
                                                Participated in an ESL program
                                                </label>
                                                <!-- condition description 3 -->
                                                <div class="no-textbox"><?=$data['condition_participated_esl_instruction']?></div>
                                            </li>
                                            <li>
                                                <label for="condition_suspended_expelled" style="display: flex; align-items: center; gap: 1rem; pointer-events: none;">
												<?php $isConditionChecked = $data['condition_suspended_expelled'] == 'Y' ? 'checked' : null?>
												<div class="no-checkbox-form" style="display: inline-block;">
													<input type="checkbox" class="no-input--detail" <?=$isConditionChecked?>>
													<span>
														<i class="bx bxs-check-square"></i>
													</span>
												</div>
                                                Suspended or Expelled from a school
                                                </label>
                                                <!-- condition description 4 -->
                                                <div class="no-textbox"><?=$data['condition_suspended_expelled_resolved']?></div>
                                            </li>
                                        </ul>
                                        <ul class="block border list">
                                            <li>
                                                <label for="condition_diagnosed_learning" style="display: flex; align-items: center; gap: 1rem; pointer-events: none;">
												<?php $isConditionChecked = $data['condition_diagnosed_learning'] == 'Y' ? 'checked' : null?>
												<div class="no-checkbox-form" style="display: inline-block;">
													<input type="checkbox" class="no-input--detail" <?=$isConditionChecked?>>
													<span>
														<i class="bx bxs-check-square"></i>
													</span>
												</div>
                                                Diagnosed with a learning disability
                                                </label>
                                            </li>
                                            <li>
                                                <label for="special_reading_disability" style="display: flex; align-items: center; gap: 1rem; pointer-events: none;">
												<?php $isConditionChecked = $data['special_reading_disability'] == 'Y' ? 'checked' : null?>
												<div class="no-checkbox-form" style="display: inline-block;">
													<input type="checkbox" id="special_reading_disability" class="no-input--detail" <?=$isConditionChecked?>>
													<span>
														<i class="bx bxs-check-square"></i>
													</span>
												</div>

                                                Reading disability
                                                </label>
                                            </li>
                                            <li>
                                                <label for="special_writing_disability" style="display: flex; align-items: center; gap: 1rem; pointer-events: none;">
												<?php $isConditionChecked = $data['special_writing_disability'] == 'Y' ? 'checked' : null?>
												<div class="no-checkbox-form" style="display: inline-block;">
													<input type="checkbox" class="no-input--detail" <?=$isConditionChecked?>>
													<span>
														<i class="bx bxs-check-square"></i>
													</span>
												</div>
                                                Writing disability
                                                </label>
                                            </li>
                                            <li>
                                                <label for="special_mathematics_disability" style="display: flex; align-items: center; gap: 1rem; pointer-events: none;">
												<?php $isConditionChecked = $data['special_mathematics_disability'] == 'Y' ? 'checked' : null?>
												<div class="no-checkbox-form" style="display: inline-block;">
													<input type="checkbox" class="no-input--detail" <?=$isConditionChecked?>>
													<span>
														<i class="bx bxs-check-square"></i>
													</span>
												</div>
                                                Mathematics disability
                                                </label>
                                            </li>
                                            <li>
                                                <label for="special_attention_deficit" style="display: flex; align-items: center; gap: 1rem; pointer-events: none;">
												<?php $isConditionChecked = $data['special_attention_deficit'] == 'Y' ? 'checked' : null?>
												<div class="no-checkbox-form" style="display: inline-block;">
													<input type="checkbox" class="no-input--detail" <?=$isConditionChecked?>>
													<span>
														<i class="bx bxs-check-square"></i>
													</span>
												</div>
                                                Attention Deficit Hyperactive Disorder
                                                </label>
                                            </li>
                                            <li>
                                                <label for="special_other" style="display: flex; align-items: center; gap: 1rem; pointer-events: none;">
												<?php $isConditionChecked = $data['special_other'] == 'Y' ? 'checked' : null?>
												<div class="no-checkbox-form" style="display: inline-block;">
													<input type="checkbox" class="no-input--detail" <?=$isConditionChecked?>>
													<span>
														<i class="bx bxs-check-square"></i>
													</span>
												</div>
                                                Other
                                                </label>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- admin-block -->
								<!-- 끝 : Daechi Division 페이지만 나옵니다. ===================================== -->
								<?
									}
								?>
                                

                                <div class="no-items-center center">
                                    <a
                                        href="javascript:void(0);"
                                        class="no-btn no-btn--big no-btn--delete-outline"
                                        onClick="doDelete(<?=$data[no]?>);"
                                    >
                                        삭제
                                    </a>
                                    <a
                                        href="./admission.list.php"
                                        class="no-btn no-btn--big no-btn--normal"
                                    >
                                        목록
                                    </a>
                                </div>
                            </div>
                            <!-- card-body -->
                        </div>
                    </div>
					
					
					<!-- Fmaily Information ===================================================================== -->
                    <div class="no-toolbar-container no-admin-tab__block">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">Family Infromation</h2>
                            </div>
                            <div
                                class="no-card-body no-admin-column no-admin-column--detail"
                            >

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="marital_status_of_parents">Marital Status of Parents</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="text"
                                            name="marital_status_of_parents"
                                            id="marital_status_of_parents"
                                            class="no-input--detail"
                                            value="<?=$data[marital_status_of_parents]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <strong>Parent 1 / Guardian 1</strong>
                                    </h3>
                                    <div class="no-admin-content">
                                        <ul class="list block">
                                            <li>
                                                <label for="applicant_1">Relationship to Applicant</label>
                                                <input
                                                    type="text"
                                                    name="applicant_1"
                                                    id="applicant_1"
                                                    class="no-input--detail"
                                                    value="<?=$data[parent1_relationship]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="parent1_name">Name</label>
                                                <input
                                                    type="text"
                                                    name="parent1_name"
                                                    id="parent1_name"
                                                    class="no-input--detail"
                                                    value="<?=$data[parent1_name]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="parent1_citizenship">Citizenship</label>
                                                <input
                                                    type="text"
                                                    name="parent1_citizenship"
                                                    id="parent1_citizenship"
                                                    class="no-input--detail"
                                                    value="<?=$data[parent1_citizenship]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="parent1_occupation">Occupation/Position</label>
                                                <input
                                                    type="text"
                                                    name="parent1_occupation"
                                                    id="parent1_occupation"
                                                    class="no-input--detail"
                                                    value="<?=$data[parent1_occupation]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="parent1_tel">Tel(Work)</label>
                                                <input
                                                    type="text"
                                                    name="parent1_tel"
                                                    id="parent1_tel"
                                                    class="no-input--detail"
                                                    value="<?=$data[parent1_tel]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="parent_phone_1">Mobile Phone</label>
                                                <input
                                                    type="text"
                                                    name="parent_phone_1"
                                                    id="parent_phone_1"
                                                    class="no-input--detail"
                                                    value="<?=$data[parent1_mobile_phone]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="parent_email_1">Email Address</label>
                                                <input
                                                    type="text"
                                                    name="parent_email_1"
                                                    id="parent_email_1"
                                                    class="no-input--detail"
                                                    value="<?=$data[parent1_email_address]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="parent1_receiving_notice">Request receiving Notices</label>
                                                <input
                                                    type="text"
                                                    name="parent1_receiving_notice"
                                                    id="parent1_receiving_notice"
                                                    class="no-input--detail"
                                                    value="<?=$data[parent1_receiving_notice]?>"
                                                    readonly
                                                />
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <strong>Parent 2 / Guardian 2</strong>
                                    </h3>
                                    <div class="no-admin-content">
                                        <ul class="list block">
                                            <li>
                                                <label for="applicant_2">Relationship to Applicant</label>
                                                <input
                                                    type="text"
                                                    name="applicant_2"
                                                    id="applicant_2"
                                                    class="no-input--detail"
                                                    value="<?=$data[parent2_relationship]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="parent2_name">Name</label>
                                                <input
                                                    type="text"
                                                    name="parent2_name"
                                                    id="parent2_name"
                                                    class="no-input--detail"
                                                    value="<?=$data[parent2_name]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="parent2_citizenship">Citizenship</label>
                                                <input
                                                    type="text"
                                                    name="parent2_citizenship"
                                                    id="parent2_citizenship"
                                                    class="no-input--detail"
                                                    value="<?=$data[parent2_citizenship]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="parent2_occupation">Occupation/Position</label>
                                                <input
                                                    type="text"
                                                    name="parent2_occupation"
                                                    id="parent2_occupation"
                                                    class="no-input--detail"
                                                    value="<?=$data[parent2_occupation]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="parent2_tel">Tel(Work)</label>
                                                <input
                                                    type="text"
                                                    name="parent2_tel"
                                                    id="parent2_tel"
                                                    class="no-input--detail"
                                                    value="<?=$data[parent2_tel]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="parent_phone_2">Mobile Phone</label>
                                                <input
                                                    type="text"
                                                    name="parent_phone_2"
                                                    id="parent_phone_2"
                                                    class="no-input--detail"
                                                    value="<?=$data[parent2_mobile_phone]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="parent_email_2">Email Address</label>
                                                <input
                                                    type="text"
                                                    name="parent_email_2"
                                                    id="parent_email_2"
                                                    class="no-input--detail"
                                                    value="<?=$data[parent2_email_address]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="parent2_receiving_notice">Request receiving Notices</label>
                                                <input
                                                    type="text"
                                                    name="parent2_receiving_notice"
                                                    id="parent2_receiving_notice"
                                                    class="no-input--detail"
                                                    value="<?=$data[parent2_receiving_notice]?>"
                                                    readonly
                                                />
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="cash_receipt">Free Cash receipt issuance</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <ul class="list block">
                                            <li>
                                                <label for="cash_receipt">type</label>
                                                <input
                                                    type="text"
                                                    name="cash_receipt"
                                                    id="cash_receipt"
                                                    class="no-input--detail"
                                                    value="<?=$data[cash_receipt]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="cash_receipt_code">number</label>
                                                <input
                                                    type="text"
                                                    name="cash_receipt_code"
                                                    id="cash_receipt_code"
                                                    class="no-input--detail"
                                                    value="<?=$data[cash_receipt_code]?>"
                                                    readonly
                                                />
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="sibling_1_name">Siblings 1</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <ul class="list block">
                                            <li>
                                                <label for="sibling_1_name">Name</label>
                                                <input
                                                    type="text"
                                                    name="sibling_1_name"
                                                    id="sibling_1_name"
                                                    class="no-input--detail"
                                                    value="<?=$data[siblings1_name]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="siblings1_gender">Gender</label>
                                                <input
                                                    type="text"
                                                    name="siblings1_gender"
                                                    id="siblings1_gender"
                                                    class="no-input--detail"
                                                    value="<?=$data[siblings1_gender]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="siblings1_grade">Grade</label>
                                                <input
                                                    type="text"
                                                    name="siblings1_grade"
                                                    id="siblings1_grade"
                                                    class="no-input--detail"
                                                    value="<?=$data[siblings1_grade]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="sibling_date_1">Date of Birth</label>
                                                <input
                                                    type="text"
                                                    name="sibling_date_1"
                                                    id="sibling_date_1"
                                                    class="no-input--detail"
                                                    value="<?=$data[siblings1_date_of_birth]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="siblings1_school_attending">School Attending</label>
                                                <input
                                                    type="text"
                                                    name="siblings1_school_attending"
                                                    id="siblings1_school_attending"
                                                    class="no-input--detail"
                                                    value="<?=$data[siblings1_school_attending]?>"
                                                    readonly
                                                />
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="sibling_2_name">Siblings 2</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <ul class="list block">
                                            <li>
                                                <label for="sibling_2_name">Name</label>
                                                <input
                                                    type="text"
                                                    name="sibling_2_name"
                                                    id="sibling_2_name"
                                                    class="no-input--detail"
                                                    value="<?=$data[siblings2_name]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="siblings2_gender">Gender</label>
                                                <input
                                                    type="text"
                                                    name="siblings2_gender"
                                                    id="siblings2_gender"
                                                    class="no-input--detail"
                                                    value="<?=$data[siblings2_gender]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="siblings2_grade">Grade</label>
                                                <input
                                                    type="text"
                                                    name="siblings2_grade"
                                                    id="siblings2_grade"
                                                    class="no-input--detail"
                                                    value="<?=$data[siblings2_grade]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="sibling_date_2">Date of Birth</label>
                                                <input
                                                    type="text"
                                                    name="sibling_date_2"
                                                    id="sibling_date_2"
                                                    class="no-input--detail"
                                                    value="<?=$data[siblings2_date_of_birth]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="siblings2_school_attending">School Attending</label>
                                                <input
                                                    type="text"
                                                    name="siblings2_school_attending"
                                                    id="siblings2_school_attending"
                                                    class="no-input--detail"
                                                    value="<?=$data[siblings2_school_attending]?>"
                                                    readonly
                                                />
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="sibling_3_name">Siblings 3</label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <ul class="list block">
                                            <li>
                                                <label for="sibling_3_name">Name</label>
                                                <input
                                                    type="text"
                                                    name="sibling_3_name"
                                                    id="sibling_3_name"
                                                    class="no-input--detail"
                                                    value="<?=$data[siblings3_name]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="siblings3_gender">Gender</label>
                                                <input
                                                    type="text"
                                                    name="siblings3_gender"
                                                    id="siblings3_gender"
                                                    class="no-input--detail"
                                                    value="<?=$data[siblings3_gender]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="siblings3_grade">Grade</label>
                                                <input
                                                    type="text"
                                                    name="siblings3_grade"
                                                    id="siblings3_grade"
                                                    class="no-input--detail"
                                                    value="<?=$data[siblings3_grade]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="sibling_date_3">Date of Birth</label>
                                                <input
                                                    type="text"
                                                    name="sibling_date_3"
                                                    id="sibling_date_3"
                                                    class="no-input--detail"
                                                    value="<?=$data[siblings3_date_of_birth]?>"
                                                    readonly
                                                />
                                            </li>
                                            <li>
                                                <label for="siblings3_school_attending">School Attending</label>
                                                <input
                                                    type="text"
                                                    name="siblings3_school_attending"
                                                    id="siblings3_school_attending"
                                                    class="no-input--detail"
                                                    value="<?=$data[siblings3_school_attending]?>"
                                                    readonly
                                                />
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- admin-block -->
                                

                                <div class="no-items-center center">
                                    <a
                                        href="javascript:void(0);"
                                        class="no-btn no-btn--big no-btn--delete-outline"
                                        onClick="doDelete(<?=$data[no]?>);"
                                    >
                                        삭제
                                    </a>
                                    <a
                                        href="./admission.list.php"
                                        class="no-btn no-btn--big no-btn--normal"
                                    >
                                        목록
                                    </a>
                                </div>
                            </div>
                            <!-- card-body -->
                            
                        </div>
                    </div>

					<!-- Additional Information ===================================================================== -->
                    <div class="no-toolbar-container no-admin-tab__block">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">Additional Comments</h2>
                            </div>
                            <div
                                class="no-card-body no-admin-column no-admin-column--detail"
                            >

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <strong>Comments</strong>
                                    </h3>
                                    <div class="no-admin-content">
                                        <div class="no-textbox">
                                            <?=$data[additional_comments]?>
                                        </div>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <strong>Portfolio</strong>
                                    </h3>
                                    <div class="no-admin-content file">
                                        <ul class="list block">
                                            <li>
                                                <a href="/inc/lib/admission.file.download.php?no=<?=$data[no]?>&fld=p1" class="no-btn no-btn--main">첨부파일1 다운로드</a>
                                            </li>
                                            <li>
                                                <a href="/inc/lib/admission.file.download.php?no=<?=$data[no]?>&fld=p2" class="no-btn no-btn--main">첨부파일2 다운로드</a>
                                            </li>
                                            <li>
                                                <a href="/inc/lib/admission.file.download.php?no=<?=$data[no]?>&fld=p3" class="no-btn no-btn--main">첨부파일3 다운로드</a>
                                            </li>
                                            <li>
                                                <a href="/inc/lib/admission.file.download.php?no=<?=$data[no]?>&fld=p4" class="no-btn no-btn--main">첨부파일4 다운로드</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <strong>How did you hear about SA?</strong>
                                    </h3>
                                    <div class="no-admin-content">
										<ul class="marker">
											<?php $hear_about_list = explode(',', $data[about_sa]); 
											foreach ($hear_about_list as $k => $v): ?>
											<li><?=$v?></li>
											<?php endforeach; ?>
										</ul>
                                    </div>
                                </div>
                                <!-- admin-block -->

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">
                                        <label for="attd_date">
                                        Desired Starting Date of School Attendance
                                        </label>
                                    </h3>
                                    <div class="no-admin-content">
                                        <input
                                            type="date"
                                            name="attd_date"
                                            id="attd_date"
                                            class="no-input--detail"
                                            value="<?=$data[desired_starting_date]?>"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- admin-block -->

                             
                                

                                <div class="no-items-center center">
                                    <a
                                        href="javascript:void(0);"
                                        class="no-btn no-btn--big no-btn--delete-outline"
                                        onClick="doDelete(<?=$data[no]?>);"
                                    >
                                        삭제
                                    </a>
                                    <a
                                        href="./admission.list.php"
                                        class="no-btn no-btn--big no-btn--normal"
                                    >
                                        목록
                                    </a>
                                </div>
                            </div>
                            <!-- card-body -->
                        </div>
                    </div>

                </section>
            </form>
        </main>



        <!-- Footer -->
        <script type="text/javascript" src="./js/process.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"></script>
        <?
            include_once "../../inc/admin.footer.php";
        ?>
	
    </div>
    <script>
        const tabHead = $('.no-admin-tab__item');
        const tabBody = $('.no-admin-tab__block');

        console.log(tabHead);
        console.log(tabBody);

        tabBody.hide();
        tabHead.eq(0).addClass('active')
        tabBody.eq(0).show();

        tabHead.click(function(){
            const index = $(this).index();

            $(this).addClass('active');
            $(this).siblings().removeClass('active')

            tabBody.hide();
            tabBody.eq(index).show();
        })
    </script>
</body>
</html>