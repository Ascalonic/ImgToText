/*jslint browser: true, white: true, eqeq: true, plusplus: true, sloppy: true, vars: true*/
/*global $, console, alert, FormData, FileReader*/

var canvas = document.getElementById('myCanvas'),
ctx = canvas.getContext('2d'),
rect = {},
drag = false;
var img = new Image;

function noPreview() {
  $('#image-preview-div').css("display", "none");
  $('#preview-img').attr('src', 'noimage');
  $('upload-button').attr('disabled', '');
}

function selectImage(e) {
  $('#file').css("color", "green");
  $('#image-preview-div').css("display", "block");
  $('#preview-img').attr('src', e.target.result);
  $('#preview-img').css('max-width', '550px');
}

function init() {
  canvas.addEventListener('mousedown', mouseDown, false);
  canvas.addEventListener('mouseup', mouseUp, false);
  canvas.addEventListener('mousemove', mouseMove, false);
}

function mouseDown(e) {
  rect.startX = e.pageX - this.offsetLeft;
  rect.startY = e.pageY - this.offsetTop;
  drag = true;
}

function mouseUp() {
  drag = false;
}

function mouseMove(e) {
  if (drag) {
    rect.w = (e.pageX - this.offsetLeft) - rect.startX;
    rect.h = (e.pageY - this.offsetTop) - rect.startY ;
    ctx.clearRect(0,0,canvas.width,canvas.height);
    draw();
  }
}

function draw() {
  ctx.drawImage(img,0,0);
  ctx.globalAlpha = 0.2;
  ctx.fillRect(rect.startX, rect.startY, rect.w, rect.h);
  ctx.globalAlpha = 1.0;
}

$(document).ready(function (e) {

  var maxsize = 8056 * 1024; // 500 KB

  $('#max-size').html((maxsize/1024).toFixed(2));
  $('#filecrop').hide();
  $('#conv_result').hide();

  $('#upload-image-form').on('submit', function(e) {

    e.preventDefault();

    $('#message').empty();
    $('#loading').show();

    $.ajax({
      url: "upload-image.php",
      type: "POST",
      data: new FormData(this),
      contentType: false,
      cache: false,
      processData: false,
      success: function(data)
      {
		  $('#fileupload').hide();
		  $('#filecrop').show();
		  $('#loading').hide();
		  //document.getElementById('ret_json').innerHTML = data;
		  
		  img_attr_obj = JSON.parse(data);
		  
		  document.getElementById("myCanvas").setAttribute("width", img_attr_obj['file_width']);
		  document.getElementById("myCanvas").setAttribute("height", img_attr_obj['file_height']);
		  
		  var c=document.getElementById("myCanvas");
		  var ctx=c.getContext("2d");
		  img.onload = function(){
			  ctx.drawImage(img,0,0); // Or at whatever offset you like
			};
		  img.src = img_attr_obj['file_path'];	  
		  init();
      }
    });

  });
  
  $("#convert-button").click(function(){
	
	$('#converting').show();
	console.log('hello');
	  
    $.post("convert",
    {
        startx:rect.startX,
		starty:rect.startY,
		width:rect.w,
		height:rect.h
    },
    function(data, status){
		$('#converting').hide();
		$('#conv_result').show();
		resp = JSON.parse(data);
		console.log('Here you go:' + data);
        document.getElementById('conv_result').innerHTML = resp['text'];
    });
})
  


  $('#file').change(function() {

    $('#message').empty();

    var file = this.files[0];
    var match = ["image/jpeg", "image/png", "image/jpg"];

    if ( !( (file.type == match[0]) || (file.type == match[1]) || (file.type == match[2]) ) )
    {
      noPreview();

      $('#message').html('<div class="alert alert-warning" role="alert">Unvalid image format. Allowed formats: JPG, JPEG, PNG.</div>');

      return false;
    }

    if ( file.size > maxsize )
    {
      noPreview();

      $('#message').html('<div class=\"alert alert-danger\" role=\"alert\">The size of image you are attempting to upload is ' + (file.size/1024).toFixed(2) + ' KB, maximum size allowed is ' + (maxsize/1024).toFixed(2) + ' KB</div>');

      return false;
    }

    $('#upload-button').removeAttr("disabled");

    var reader = new FileReader();
    reader.onload = selectImage;
    reader.readAsDataURL(this.files[0]);

  });

});

