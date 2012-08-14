<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>ImgEditor</title>


    <!-- Depedencies -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/themes/base/jquery-ui.css" type="text/css" media="all" />

    <script src="js/draggable_patch.js"></script>
    <script src="js/json2.js"></script>
    <script src="js/jqueryrotate.js"></script>

    <!-- Plugin -->
    <script src="js/ImgEditor.js?<? echo time()?>"></script> 
    <link rel="stylesheet" href="css/ImgEditor.css" type="text/css" media="all" /> 

    <script type="text/javascript">
        $(document).ready(function(){
            ImgEditor.settings = {
                editor : "editor", // Name of Editor DIV
                canvas_height : '400px',
                canvas_width : '400px',
                background_pic : '', //'img/bg.jpg',
                frame_pic : '',//'img/frame1.png',
                frame_height : '400px',
                frame_width : '400px',
                onSave_callback : function(response){console.log('saved > ' + response) } // Function called aftes image saved
            };

            ImgEditor.init.canvas();
            ImgEditor.init.images();
            ImgEditor.init.tools();
            ImgEditor.init.frame();
        });
    </script>


</head>

<body>

    <button onClick="ImgEditor.resetCanvas()">
         Reset Canvas
    </button>

    <input type="text" id="url" value="http://windycitizensports.files.wordpress.com/2012/06/miami-dolphins-cheerleaders-call-me-maybe.jpg?w=300">
    <button onClick="ImgEditor.appendImage($('#url').val());">
         Append Img
    </button>

    <button onClick="ImgEditor.save();">
         Save
    </button>



    <div id="editor">
        <img src="http://windycitizensports.files.wordpress.com/2012/06/miami-dolphins-cheerleaders-call-me-maybe.jpg?w=300">
    </div>           
    <div id="tools">
        <div id="angle"></div>
        <div id="size"></div>
    </div>



</body>
</html>