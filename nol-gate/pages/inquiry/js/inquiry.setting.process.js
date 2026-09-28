function doRentalSettingSave() {
  const form = document.getElementById("frm");
  // 대관 신청 안내 문구 Summernote 내용 동기화
  var $rentalNotice = $("#rental_notice");
  if ($rentalNotice.length && $rentalNotice.data("summernote")) {
    $rentalNotice.val($rentalNotice.summernote("code"));
  }
  // 접수페이지 NOTICE Summernote 내용 동기화
  var $applyNotice = $("#rental_apply_notice");
  if ($applyNotice.length && $applyNotice.data("summernote")) {
    $applyNotice.val($applyNotice.summernote("code"));
  }
  const formData = new FormData(form);
  formData.append("mode", "rental.setting.save");

  // 유효성 검사
  const rentalIsOpen = form.querySelector(
    'input[name="rental_is_open"]:checked'
  )?.value;
  const rentalStartDate = form.querySelector(
    'input[name="rental_start_date"]'
  ).value;
  const rentalEndDate = form.querySelector(
    'input[name="rental_end_date"]'
  ).value;

  if (rentalIsOpen == "1") {
    if (rentalStartDate && rentalEndDate) {
      if (new Date(rentalStartDate) > new Date(rentalEndDate)) {
        alert("시작일은 종료일보다 이전이어야 합니다.");
        return;
      }
    }
  }

  $.ajax({
    url: "./ajax/inquiry.setting.process.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (response) {
      if (response.result === "success") {
        alert(response.msg);
        location.reload();
      } else {
        alert(response.msg || "저장 중 오류가 발생했습니다.");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error:", error);
      alert("저장 중 오류가 발생했습니다. 다시 시도해주세요.");
    },
  });
}

