function doRegSave() {
  if ($("#skin").val() == "") {
    alert("게시판 타입을 선택해주세요");
    $("#skin").focus();
    return;
  }

  if ($("#title").val() == "") {
    alert("게시판 이름을 입력해주세요");
    $("#title").focus();
    return;
  }

  $("#mode").val("save");

  var params = jQuery("#frm").serialize();

  // Get form
  var form = $("#frm")[0];

  // Create an FormData object
  var data = new FormData(form);

  $.ajax({
    type: "POST",
    enctype: "multipart/form-data",
    url: "./ajax/board.manage.process.php",
    data: data,
    processData: false,
    contentType: false,
    cache: false,
    success: function (data) {
      var jsonData = $.parseJSON(data);

      if (jsonData.result == "fail") {
        alert(jsonData.msg);
      } else if (jsonData.result == "success") {
        alert(jsonData.msg);
        location.href = "./board.manage.list.php";
      }
    },
    error: function (e) {},
    complete: function () {},
  });
}

function doEditSave() {
  if ($("#skin").val() == "") {
    alert("게시판 타입을 선택해주세요");
    $("#skin").focus();
    return;
  }

  if ($("#title").val() == "") {
    alert("게시판 이름을 입력해주세요");
    $("#title").focus();
    return;
  }

  $("#mode").val("edit");

  var params = jQuery("#frm").serialize();

  // Get form
  var form = $("#frm")[0];

  // Create an FormData object
  var data = new FormData(form);

  $.ajax({
    type: "POST",
    enctype: "multipart/form-data",
    url: "./ajax/board.manage.process.php",
    data: data,
    processData: false,
    contentType: false,
    cache: false,
    success: function (data) {
      var jsonData = $.parseJSON(data);

      if (jsonData.result == "fail") {
        alert(jsonData.msg);
      } else if (jsonData.result == "success") {
        alert(jsonData.msg);
        location.href = "./board.manage.list.php";
      }
    },
    error: function (e) {},
    complete: function () {},
  });
}

function doDelete(no) {
  var con = confirm("정말 삭제하시겠습니까?");

  var param = "";
  if (no) {
    param = "no=" + no + "&mode=delete";
  } else {
    param = "no=" + $("#no").val() + "&mode=delete";
  }

  if (con) {
    $.ajax({
      type: "POST",
      url: "./ajax/board.manage.process.php",
      data: param,
      cache: false,
      dataType: "html",
      success: function (data) {
        var jsonData = $.parseJSON(data);

        if (jsonData.result == "fail") {
          alert(jsonData.msg);
        } else if (jsonData.result == "success") {
          alert(jsonData.msg);
          location.href = "./board.manage.list.php";
        }
      },
      error: function (a, s) {
        alert("처리중 문제가 발생하였습니다.");
        return;
      },
    });
  }
}

function doCategoryAdd() {
  if ($("#name").val() == "") {
    alert("카테고리 이름을 입력해주세요");
    $("#name").focus();
    return;
  }

  $("#mode").val("category.add");

  var params = jQuery("#frm").serialize();

  // Get form
  var form = $("#frm")[0];

  // Create an FormData object
  var data = new FormData(form);

  $.ajax({
    type: "POST",
    enctype: "multipart/form-data",
    url: "./ajax/board.manage.process.php",
    data: data,
    processData: false,
    contentType: false,
    cache: false,
    success: function (data) {
      var jsonData = $.parseJSON(data);

      if (jsonData.result == "fail") {
        alert(jsonData.msg);
      } else if (jsonData.result == "success") {
        alert(jsonData.msg);
        location.reload();
      }
    },
    error: function (e) {},
    complete: function () {},
  });
}

function doCategorySave(n) {
  if ($("#name_" + n).val() == "") {
    alert("카테고리 이름을 입력해주세요");
    $("#name_" + n).focus();
    return;
  }

  if ($("#sort_no_" + n).val() == "") {
    alert("순서를 숫자로 입력해주세요");
    $("#sort_no_" + n).focus();
    return;
  }

  var name = encodeURI($("#name_" + n).val());
  var sort_no = $("#sort_no_" + n).val();

  param =
    "no=" + n + "&mode=category.save&name=" + name + "&sort_no=" + sort_no;

  $.ajax({
    type: "POST",
    url: "./ajax/board.manage.process.php",
    data: param,
    cache: false,
    dataType: "html",
    success: function (data) {
      var jsonData = $.parseJSON(data);

      if (jsonData.result == "fail") {
        alert(jsonData.msg);
      } else if (jsonData.result == "success") {
        alert(jsonData.msg);
        location.reload();
      }
    },
    error: function (a, s) {
      alert("처리중 문제가 발생하였습니다.");
      return;
    },
  });
}

function doCategoryDelete(no) {
  var con = confirm("정말 삭제하시겠습니까?");

  var param = "";
  if (no) {
    param = "no=" + no + "&mode=category.delete";
  } else {
    param = "no=" + $("#no").val() + "&mode=category.delete";
  }

  if (con) {
    $.ajax({
      type: "POST",
      url: "./ajax/board.manage.process.php",
      data: param,
      cache: false,
      dataType: "html",
      success: function (data) {
        var jsonData = $.parseJSON(data);

        if (jsonData.result == "fail") {
          alert(jsonData.msg);
        } else if (jsonData.result == "success") {
          alert(jsonData.msg);
          location.reload();
        }
      },
      error: function (a, s) {
        alert("처리중 문제가 발생하였습니다.");
        return;
      },
    });
  }
}

function changeSelectAction(elementId, $value) {
  const element = $(`#${elementId}`);

  element.selectmenu($value);
}
