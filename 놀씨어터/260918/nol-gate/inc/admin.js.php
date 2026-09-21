<!-- jQuery -->
<script>
window.NO_ADMIN_BASE = <?= json_encode($NO_ADMIN_BASE) ?>;
window.NO_ADMIN_PAGES_BASE = <?= json_encode($NO_ADMIN_PAGES_BASE) ?>;
window.NO_ADMIN_RESOURCE_BASE = <?= json_encode($NO_ADMIN_RESOURCE_BASE) ?>;
</script>
<script type="text/javascript" src="<?=$NO_IS_SUBDIR?>/resource/vendor/jquery/jquery.min.js"></script>
<script type="text/javascript" src="<?=$NO_IS_SUBDIR?>/resource/vendor/jquery/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!--  js  -->
<script type="text/javascript" src="<?=$NO_ADMIN_RESOURCE_BASE?>/js/admin.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"
    defer></script>
<script type="text/javascript" src="<?=$NO_ADMIN_RESOURCE_BASE?>/js/script.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"
    defer></script>

<script type="text/javascript" src="<?=$NO_ADMIN_RESOURCE_BASE?>/js/form.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"
    defer></script>
<script type="text/javascript" src="<?=$NO_ADMIN_RESOURCE_BASE?>/js/theme.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"
    defer></script>


<!-- <script type="text/javascript" src="<?=$NO_IS_SUBDIR?>/resource/js/html5shiv.js?v=<?=$STATIC_FRONT_JS_MODIFY_DATE?>"></script>
<script type="text/javascript" src="<?=$NO_IS_SUBDIR?>/resource/js/prefixfree.min.js?v=<?=$STATIC_FRONT_JS_MODIFY_DATE?>"></script> -->

<!-- [if it IE 9]>
<script type="text/javascript" src="<?=$NO_IS_SUBDIR?>/resource/js/html5shiv.js"></script>
<![endif]-->
<!-- <script lang="javascript" src="<?=$NO_IS_SUBDIR?>/resource/vendor/sheetjs/shim.min.js"></script>
<script lang="javascript" src="<?=$NO_IS_SUBDIR?>/resource/vendor/sheetjs/xlsx.full.min.js"></script> -->
<!-- <script type="text/javascript" src="<?=$NO_IS_SUBDIR?>/resource/js/prefixfree.min.js"></script> -->

<!-- <script type="text/javascript" src="<?=$NO_IS_SUBDIR?>/resource/js/common.ajax.lib.js?v=<?=$STATIC_FRONT_JS_MODIFY_DATE?>"></script> -->
<!-- <script type="text/javascript" src="<?=$NO_IS_SUBDIR?>/resource/vendor/filter-multi-select/filter-multi-select-bundle.min.js"></script> -->


<!-- Summernote -->
<script src="<?=$NO_IS_SUBDIR?>/resource/vendor/summernote/bootstrap.min.js"></script>
<script src="<?=$NO_IS_SUBDIR?>/resource/vendor/summernote/summernote.min.js"></script>
<script src="<?=$NO_IS_SUBDIR?>/resource/vendor/summernote/lang/summernote-ko-KR.js"></script>
<script src="<?= $ROOT ?>/resource/vendor/fontAwesome/fontAwesome.min.js" crossorigin="anonymous"></script>
<script src="<?=$NO_ADMIN_RESOURCE_BASE?>/js/summernote-size-hint.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"></script>


<!-- add custom -->
<!-- <script type="text/javascript" src="<?=$NO_IS_SUBDIR?>/resource/js/datepicker.custom.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"></script> -->
<script type="module" src="<?=$NO_ADMIN_RESOURCE_BASE?>/js/app.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>" defer>
</script>
