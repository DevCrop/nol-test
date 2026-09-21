export function previewImage(input, imgId, txtId) {
  const file = input.files[0];
  const preview = document.getElementById(imgId);
  const fakeInput = document.getElementById(txtId);

  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      preview.src = e.target.result;
      preview.style.display = "block";
    };
    reader.readAsDataURL(file);
    fakeInput.value = file.name;
  } else {
    preview.src = "";
    preview.style.display = "none";
    fakeInput.value = "";
  }
}

// 배너 파일 미리보기 (이미지/비디오) — 데스크탑/모바일 각각 해당 컨테이너 사용
export function previewBannerFile(input, previewId, txtId) {
  const file = input.files[0];
  const containerId = previewId === "bannerMobilePreview" ? "bannerMobilePreviewContainer" : "bannerPreviewContainer";
  const container = document.getElementById(containerId);
  const fakeInput = document.getElementById(txtId);

  if (file) {
    const fileType = file.type;
    const isVideo = fileType.startsWith("video/");
    const isImage = fileType.startsWith("image/");

    // 기존 미리보기 삭제
    container.innerHTML = "";

    if (isVideo) {
      // 비디오 미리보기
      const video = document.createElement("video");
      video.id = previewId;
      video.controls = true;
      video.style.maxWidth = "300px";
      video.style.marginTop = "10px";

      const reader = new FileReader();
      reader.onload = function (e) {
        video.src = e.target.result;
      };
      reader.readAsDataURL(file);

      container.appendChild(video);
    } else if (isImage) {
      // 이미지 미리보기
      const img = document.createElement("img");
      img.id = previewId;
      img.alt = "배너 미리보기";
      img.style.maxWidth = "150px";
      img.style.marginTop = "10px";

      const reader = new FileReader();
      reader.onload = function (e) {
        img.src = e.target.result;
      };
      reader.readAsDataURL(file);

      container.appendChild(img);
    }

    fakeInput.value = file.name;
  } else {
    container.innerHTML =
      '<img id="' +
      previewId +
      '" src="" alt="배너 미리보기" style="display:none; max-width:150px; margin-top:10px;">';
    fakeInput.value = "";
  }
}

// 등록용
window.previewImage = previewImage;
window.previewBannerFile = previewBannerFile;
