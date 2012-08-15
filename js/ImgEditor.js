/* 
Copyright: Kriek Media Ltd.
Author: Peter Gombos

Dependencies:
- json2.js
- jQuery
- jQuery UI
- jqueryrotate


Functions:
ImgEditor.save();
ImgEditor.appendImage(url);
ImgEditor.resetCanvas()

Default Init
$(document).ready(function(){
	ImgEditor.settings = {
		editor : "editor", // Name of Editor DIV
		canvas_height : '800px',
		canvas_width : '557px',
		background_pic : '', //'img/bg.jpg',
		frame_pic : 'img/front.png',
		frame_height : '800px',
		frame_width : '557px',
		onSave_callback : append // Function called aftes image saved
	};
	ImgEditor.init.canvas();
	ImgEditor.init.images();
	ImgEditor.init.tools();
	ImgEditor.init.frame();
});
*/

var ImgEditor = {
	images : [],
	selectedIndex : [],
	maxZindex : 1,
	isIE : function(){
		var supportedCSS,styles=document.getElementsByTagName("head")[0].style,toCheck="transformProperty WebkitTransform OTransform msTransform MozTransform".split(" ");
		for (var a=0;a<toCheck.length;a++) if (styles[toCheck[a]] !== undefined) supportedCSS = toCheck[a];
		// Bad eval to preven google closure to remove it from code o_O
		// After compresion replace it back to var IE = 'v' == '\v'
		return eval('"v"=="\v"');
	},
	init : {
		canvas : function(){
			var trasparent_class = ".transparent { zoom: 1; filter: alpha(opacity=30); opacity: 0.3; z-index:9999 !important; }";
			var editor_style = "#editor{padding:0px;position:relative;overflow:hidden; height:" + ImgEditor.settings.canvas_height + "; width:" + ImgEditor.settings.canvas_width + '; } #editor img{position:absolute;}';
			$('<style>' + trasparent_class + editor_style  +'</style>').appendTo('head');
		},
		images : function(){
			
			ImgEditor.i = 0;
			ImgEditor.images = [];
			
			ImgEditor.selectedIndex.push(0,0);
			
			$("#" + ImgEditor.settings.editor + " img").draggable({
				cursor: "move",
				snap : true,
				start : function(){
					ImgEditor.images[ImgEditor.selectedIndex[1]].top = Math.ceil($('#' + ImgEditor.selectedId).position().top) + 'px';
					ImgEditor.images[ImgEditor.selectedIndex[1]].left = Math.ceil($('#' + ImgEditor.selectedId).position().left) + 'px';
				},
				stop : function() {
					ImgEditor.images[ImgEditor.selectedIndex[1]].top = Math.ceil($('#' + ImgEditor.selectedId).position().top) + 'px';
					ImgEditor.images[ImgEditor.selectedIndex[1]].left = Math.ceil($('#' + ImgEditor.selectedId).position().left) + 'px';
					
				}
			});
			
			$("#" + ImgEditor.settings.editor + " img").each(function(){
				
				var Img = $(this).attr("src",$(this).attr('src')+ "?" + new Date().getTime());
				
				$(Img).load(function(){
					$(this).attr('id','pic_' + ImgEditor.i);
				
					var data = {
						id :  'pic_' + ImgEditor.i,
						height :  this.height,
						width : this.width,
						src : this.src,
						top : Math.ceil($('#pic_' + ImgEditor.i).position().top) + 'px',
						left : Math.ceil($('#pic_' + ImgEditor.i).position().left) + 'px',
						angle : 0,
						size: 100,
						zIndex : 1,
						filph : false
					};
					
					ImgEditor.images.push( data );
					
					ImgEditor.i++;
					
				});
			});
			
			if(ImgEditor.i > 0){ $('#controller').show(); }
			
			ImgEditor.selectedId = 'pic_' + ImgEditor.i;
			ImgEditor.selectedIndex[1] = ImgEditor.i;
			
			$("#" + ImgEditor.settings.editor + " img").bind('mouseup click',function(){
				ImgEditor.selectedId = $(this).attr('id');
				ImgEditor.selectedIndex = ImgEditor.selectedId.split("_");
				$("#size").slider({value: ImgEditor.images[ImgEditor.selectedIndex[1]].size});
				$("#angle").slider({value: ImgEditor.images[ImgEditor.selectedIndex[1]].angle});
				
				ImgEditor.images[ImgEditor.selectedIndex[1]].zIndex = ImgEditor.maxZindex++;
				this.style.zIndex = ImgEditor.images[ImgEditor.selectedIndex[1]].zIndex;
				
			});
		},
		tools : function(){
			//FIXING IE BUG / when slider is inside a draggable element //TODO dynamic toolbar
			$('#tools').mousedown(function(e) {
				if($.browser.msie) {
					e.stopPropagation();
				}
			});

			// Init Toolbar
			$("#tools").draggable({ cursor : "move" });
			
			$("#angle").slider({
				min: -180,
				max: 180,
				value: 0,
				slide: function(){
					var angle = $(this).slider("value");
					ImgEditor.rotate(angle);
					
				},
				start: function(){
					
				},
				stop: function(){
					var angle = $(this).slider("value");
					ImgEditor.rotate(angle);

					ImgEditor.images[ImgEditor.selectedIndex[1]].angle = $(this).slider("value");
				
					if(ImgEditor.isIE()){
						$('#' + ImgEditor.selectedId).draggable({
							stop : function() {
								ImgEditor.images[ImgEditor.selectedIndex[1]].top = Math.ceil($('#' + ImgEditor.selectedId).position().top) + 'px';
								ImgEditor.images[ImgEditor.selectedIndex[1]].left = Math.ceil($('#' + ImgEditor.selectedId).position().left) + 'px';
							}
						});
						
						ImgEditor.reBind();
						
					}
					
					
					
					ImgEditor.images[ImgEditor.selectedIndex[1]].top = Math.ceil($('#' + ImgEditor.selectedId).position().top) + 'px';
					
					ImgEditor.images[ImgEditor.selectedIndex[1]].left = Math.ceil($('#' + ImgEditor.selectedId).position().left) + 'px';
					
				}
			});
			$("#size").slider({
				min: 0,
				max: 200,
				value: 100,
				slide: function(){
					var percent = $(this).slider("value");
					ImgEditor.resize(percent);
					
					if(ImgEditor.isIE()){
						ImgEditor.reRender();
					}
				},
				start: function(){
						
				},
				stop: function(){
					var percent = $(this).slider("value");
					ImgEditor.resize(percent);
					
					if(ImgEditor.isIE()){
						ImgEditor.reRender();
					}
					
					ImgEditor.images[ImgEditor.selectedIndex[1]].size = $(this).slider("value");

					if(ImgEditor.isIE()){
						$('#' + ImgEditor.selectedId).draggable({
							stop : function() {
								ImgEditor.images[ImgEditor.selectedIndex[1]].top = Math.ceil($('#' + ImgEditor.selectedId).position().top) + 'px';
								ImgEditor.images[ImgEditor.selectedIndex[1]].left = Math.ceil($('#' + ImgEditor.selectedId).position().left) + 'px';
							}
						});
						
						ImgEditor.reBind();
					}
					
					ImgEditor.images[ImgEditor.selectedIndex[1]].top = Math.ceil($('#' + ImgEditor.selectedId).position().top) + 'px';
					ImgEditor.images[ImgEditor.selectedIndex[1]].left = Math.ceil($('#' + ImgEditor.selectedId).position().left) + 'px';
				}
			});
		},
		frame : function(){
			var frame_div = document.createElement("div");
			frame_div.style.backgroundImage = 'url(' + ImgEditor.settings.frame_pic + ')';
			frame_div.style.zIndex = 9998;
			frame_div.style.position = 'absolute';
			frame_div.style.width = ImgEditor.settings.frame_width;
			frame_div.style.height = ImgEditor.settings.frame_height;
			frame_div.id = 'frame';
			
			//Mouse inside canvas
			$('#' +  ImgEditor.settings.editor).bind('mouseenter',function(){
				$('#' +  ImgEditor.settings.editor + ' img').addClass('transparent');
			});
			
			$('#' +  ImgEditor.settings.editor).bind('mouseleave',function(){
				$('#' +  ImgEditor.settings.editor + ' img').removeClass('transparent');
			});
			
			//Mouse button down on controller
			$('#angle,#size').bind('mousedown',function(){
				$('#' +  ImgEditor.settings.editor + ' img').addClass('transparent');
			});
			
			$('#angle,#size').bind('mouseup',function(){
				$('#' +  ImgEditor.settings.editor + ' img').removeClass('transparent');
			});
			
			
			//Append frame
			$('#' +  ImgEditor.settings.editor).prepend(frame_div);
		}
	},
	reBind: function(){
		
		$("#" + ImgEditor.selectedId).unbind('mouseup');
		$("#" + ImgEditor.selectedId).bind('mouseup click',function(){
				ImgEditor.selectedId = $(this).attr('id');
				ImgEditor.selectedIndex = ImgEditor.selectedId.split("_");
				$("#size").slider({value: ImgEditor.images[ImgEditor.selectedIndex[1]].size});
				$("#angle").slider({value: ImgEditor.images[ImgEditor.selectedIndex[1]].angle});

				ImgEditor.images[ImgEditor.selectedIndex[1]].zIndex = ImgEditor.maxZindex++;
				this.style.zIndex = ImgEditor.images[ImgEditor.selectedIndex[1]].zIndex;
		});
		
		var selector = '';
			$(ImgEditor.images).each(function(){
				selector = selector + ', #' + this.id;
		});
		selector = selector.substring(1);
		
		
		$('#' + ImgEditor.settings.editor).unbind('mouseenter mouseleave');
		
		$('#' +  ImgEditor.settings.editor).bind('mouseenter',function(){
			$(selector).addClass('transparent');
		});
			
		$('#' +  ImgEditor.settings.editor).bind('mouseleave',function(){
			$(selector).removeClass('transparent');
		});

	},
	reRender: function(){
		
		$('#' + ImgEditor.selectedId).remove();
		
		var new_img = new Image();
		new_img.src = ImgEditor.images[ImgEditor.selectedIndex[1]].src;
		new_img.id = ImgEditor.images[ImgEditor.selectedIndex[1]].id;
		new_img.style.top = ImgEditor.images[ImgEditor.selectedIndex[1]].top;
		new_img.style.left = ImgEditor.images[ImgEditor.selectedIndex[1]].left;
		new_img.style.position = 'absolute';
		new_img.style.zIndex =  ImgEditor.images[ImgEditor.selectedIndex[1]].zIndex;
		new_img.width = Math.ceil(ImgEditor.images[ImgEditor.selectedIndex[1]].width * ($('#size').slider("value") / 100));
		new_img.height = Math.ceil(ImgEditor.images[ImgEditor.selectedIndex[1]].height * ($('#size').slider("value") / 100));
		
		$('#' + ImgEditor.settings.editor).append(new_img);
		
		$('#' + ImgEditor.selectedId).rotate(ImgEditor.images[ImgEditor.selectedIndex[1]].angle);
			
	},
	rotate : function(angle){
		$('#' + ImgEditor.selectedId).rotate(Math.ceil(angle));
	},
	resize : function(percent){
		
		var height = Math.ceil(ImgEditor.images[ImgEditor.selectedIndex[1]].height * (percent / 100));
		var width = Math.ceil(ImgEditor.images[ImgEditor.selectedIndex[1]].width * (percent / 100));
		
		$('#'+ ImgEditor.selectedId ).css({
			'height' : height,
			'width'  : width
		});
		
	},
	fliph: function(){
		var img = $("#" + ImgEditor.selectedId)[0];
		Pixastic.process(img, "fliph");

		ImgEditor.images[ImgEditor.selectedIndex[1]].fliph = !ImgEditor.images[ImgEditor.selectedIndex[1]].fliph;

		$("#" + ImgEditor.selectedId).draggable({
			stop : function() {
				ImgEditor.images[ImgEditor.selectedIndex[1]].top = Math.ceil($("#" + ImgEditor.selectedId).position().top) + 'px';
				ImgEditor.images[ImgEditor.selectedIndex[1]].left = Math.ceil($("#" + ImgEditor.selectedId).position().left) + 'px';
			}
		});

		ImgEditor.reBind();

	},
	save : function() {
		$.post(
			'process_picture.php',
			{
				settings : JSON.stringify(ImgEditor.settings, null, ''),
				images : JSON.stringify(ImgEditor.images, null, '')
			},
			function(response){
				ImgEditor.settings.onSave_callback(response);
			}
		);
	},
	appendImage: function(url){
		$('#' + ImgEditor.settings.editor).append('<img src="' + url + '" id="pic_'+ImgEditor.i+'" />');
		
		$('#controller').fadeIn();
		
		var Img = $('#pic_' + ImgEditor.i).attr("src",$('#pic_' + ImgEditor.i).attr('src')+ "?" + new Date().getTime());
				
		$(Img).load(function(){
			$(this).attr('id','pic_' + ImgEditor.i);

			var data = {
					id :  'pic_' + ImgEditor.i,
					height : this.height,
					width : this.width,
					src : this.src,
					top : Math.ceil($('#pic_' + ImgEditor.i).position().top) + 'px',
					left : Math.ceil($('#pic_' + ImgEditor.i).position().left) + 'px',
					angle : 0,
					size: 100,
					zIndex : 1
			};

			ImgEditor.images.push( data );

			ImgEditor.selectedId = 'pic_' + ImgEditor.i;
			ImgEditor.selectedIndex[1] = ImgEditor.i;

			ImgEditor.reBind();

			$('#pic_' + ImgEditor.selectedIndex[1]).draggable({
					stop : function() {
						ImgEditor.images[ImgEditor.selectedIndex[1]].top = Math.ceil($('#pic_' + ImgEditor.selectedIndex[1]).position().top) + 'px';
						ImgEditor.images[ImgEditor.selectedIndex[1]].left = Math.ceil($('#pic_' + ImgEditor.selectedIndex[1]).position().left) + 'px';
					}
			});

			ImgEditor.i++;
					
		});
	},
	resetCanvas : function(){
		ImgEditor.images = [];
		$('#' +  ImgEditor.settings.editor).html('');
		if(ImgEditor.settings.frame_pic !== ''){
			ImgEditor.init.frame();
		}
	}
}