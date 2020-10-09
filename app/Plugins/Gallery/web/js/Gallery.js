var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		var Jcrop = ($.fn.Jcrop !== undefined);

		$("#content").on("click", ".btn-original", function (e) {
			reset();
			imageFactory.call(null, Gallery.large_path + "?" + Math.ceil(Math.random() * 999999), Gallery.source_width, Gallery.source_height);
			$(this).addClass("button-active").siblings(".button").removeClass("button-active");
			$("input[name='dst'], input[name='src']").val("large_path");
			
		}).on("click", ".btn-thumb", function (e) {
			reset();
			imageFactory.call(null, Gallery.small_path + "?" + Math.ceil(Math.random() * 999999), Gallery.small_width, Gallery.small_height, Gallery.small_width/Gallery.small_height);
			$(this).addClass("button-active").siblings(".button").removeClass("button-active");
			$("input[name='dst'], input[name='src']").val("small_path");
			
		}).on("click", ".btn-preview", function (e) {
			reset();
			imageFactory.call(null, Gallery.medium_path + "?" + Math.ceil(Math.random() * 999999), Gallery.medium_width, Gallery.medium_height, Gallery.medium_width/Gallery.medium_height);
			$(this).addClass("button-active").siblings(".button").removeClass("button-active");
			$("input[name='dst'], input[name='src']").val("medium_path");
			
		}).on("click", ".btn-save", function (e) {
			var $frm = $(this).closest("form");
			$.post("index.php?controller=Gallery&action=CropGallery", $frm.serialize()).done(function (data) {
				switch ($frm.find("input[name='dst']").val()) {
					case 'small_path':
						$(".btn-thumb").trigger("click");
						break;
					case 'medium_path':
						$(".btn-preview").trigger("click");
						break;
					case 'large_path':
						$(".btn-original").trigger("click");
						break;
				}
			});
			
		}).on("click", ".btn-recreate", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			var ratio,
				dst = $("input[name='dst']").val();

			if (dst == 'small_path') {
				ratio = Gallery.small_width / Gallery.small_height;
			} else if (dst == 'medium_path') {
				ratio = Gallery.medium_width / Gallery.medium_height;
			}

			imageFactory.call(null, Gallery.source_path + "?" + Math.ceil(Math.random() * 999999), Gallery.source_width, Gallery.source_height, ratio);
			$("input[name='src']").val("source_path");
			return false;
		});
		
		function reset() {
			var $frm = $("#frmMetaInfo");
        	if ($frm.length > 0) {
        		$frm.find("input[name='x']").val("");
        		$frm.find("input[name='x2']").val("");
        		$frm.find("input[name='y']").val("");
        		$frm.find("input[name='y2']").val("");
        		$frm.find("input[name='w']").val("");
        		$frm.find("input[name='h']").val("");
        	}
		}
		
		function imageFactory(src,w,h,ratio) {
			var $container = $("#crop-image");
			$container.html("");
			
			var obj = {};
			if (ratio !== undefined) {
				obj.aspectRatio = ratio;
			}
			
			$("<img>").attr("src", src).Jcrop($.extend(obj, {
				setSelect: [0, 0, w, h],
				boxWidth: 718,
				boxHeight: 539,
	            onSelect: function (c) {
	            	var $frm = $("#frmMetaInfo");
	            	if ($frm.length > 0) {
	            		$frm.find("input[name='x']").val(c.x);
	            		$frm.find("input[name='x2']").val(c.x2);
	            		$frm.find("input[name='y']").val(c.y);
	            		$frm.find("input[name='y2']").val(c.y2);
	            		$frm.find("input[name='w']").val(c.w);
	            		$frm.find("input[name='h']").val(c.h);
	            	}
	            }
	        })).appendTo($container);
		}
		
		if (Jcrop) {
			$(".btn-original").trigger("click");
		}
	});
})(jQuery_1_8_2);