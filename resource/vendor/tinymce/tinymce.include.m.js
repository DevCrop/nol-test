	tinymce.init({
	    selector: "textarea[geditor]",
	    theme: "modern",
	    language : 'ko_KR',
	    height: "150",
	    force_br_newlines : false,
	    force_p_newlines : true,
	    convert_newlines_to_brs : false,
	    remove_linebreaks : true,
	    forced_root_block : 'p', // Needed for 3.x
	    relative_urls:false,
	    allow_script_urls: true,
	    remove_script_host: false,
	    convert_urls: false,
	    formats: { bold : {inline : 'b' }},
	    extended_valid_elements: "@[class|id|width|height|alt|href|style|rel|cellspacing|cellpadding|border|src|name|title|type|onclick|onfocus|onblur|target],b,i,em,strong,a,img,br,h1,h2,h3,h4,h5,h6,div,table,tr,td,s,del,u,p,span,article,section,header,footer,svg,blockquote,hr,ins,ul,dl,object,embed,pre",
	    plugins: [
	        "jbimages",
	         "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
	         "searchreplace visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
	         "save table contextmenu directionality emoticons template paste textcolor"
	   ],
	   content_css: "/pages/css/editor.css",
	   body_class: "editor_content",
	   menubar : false,
	   toolbar1: "alignleft aligncenter alignright | outdent indent | advlist bold italic forecolor backcolor | jbimages | autolink",
	 }); 
$(document).ready(function(){
	// 에디터 작성시 validate 조정
	$('form').submit(function(){ tinyMCE.triggerSave(); });
});