(function ($){
  jQuery("document").ready(function(){
	  var direccionLeerResenas = "../api/1.0.0/interfaz/proyecto/leer-resenas/";
	  var direccionEnviarResena = "../api/1.0.0/interfaz/proyecto/enviar-resena/";
	  var enviandoCalificacion = false;
	  var calificacion = 1;
	  var resenaId = 0;
	  $('#comentarios').html('');
	  
	  $.urlParam = function(name){
		    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
		    if (results==null){
		       return null;
		    }
		    else{
		       return decodeURI(results[1]) || 0;
		    }
		}
	  
	  var id = $.urlParam('dream');
	  var admin = false;
	  
	  var leerResenaRespondio = function (datos){
		  console.log(datos);
	        if(datos.status == "ok"){
	        	llenarResenas(datos.resenas);
	        } else{

	        }
	  }
	  
	  var leerResenaError = function (datos) {
		  console.log(datos);
		  mostrarError("Error con el servidor");
	  }
	  
	  
	  var token = '';
	  if((token = leerToken('dreamer')) != null) {
		  var parametrosResena = {token: token, id: id, usuario: 0};
		  $('#forma-calificar').show();
		  
	  } else if ((token = leerToken('socio')) != null) {
		  var parametrosResena = {token: token, id: id, usuario: 1};
		  $('#forma-calificar').hide();		  
	  } else {
		  token = leerToken('admin');
		  admin = true;
		  var parametrosResena = {token: token, id: id, usuario: 2};
		  $('#forma-calificar').hide();
	  }
	  
	  $.post(direccionLeerResenas,parametrosResena, leerResenaRespondio,"json").fail(leerResenaError);
	  
		  
	  function llenarResenas(resenas) {
		  var resenasHTML = "";
		  $.each(resenas, function(index, resena) {
			  resenasHTML += "<li>" +
			  		"<div class='dream-comment-section'>" +
			  		"<div class='main-comment w-clearfix'>" +
			  		"<h4 class='title-comment'>" + resena.titulo + "</h4>" +
			  		"<h5 class='comment-user'>@" + resena.nombreDeUsuario + "</h5>";
			  var calificacion = Math.round(resena.calificacion);
			  for (var i=0; i < calificacion; i++)
			  {
				  resenasHTML += "<img class='comment-rating' sizes='(max-width: 767px) 25px, (max-width: 991px) 3vw, 25px' src='../images/star.png' srcset='../images/star-p-500.png 500w, ../images/star-p-800.png 800w, ../images/star-p-1080.png 1080w, ../images/star.png 1211w' width='25'>";
			  }
			  	
			  resenasHTML += "<p class='comment-opinion'>" + resena.comentario + "</p>" +
			  		"</div>" +
			  		"<ul class='subcomentarios-lista w-list-unstyled' id='sub" + resena.id + "'>" +
			  		"</ul>" +
			  		"<div class='w-clearfix'>" +
			  		"<p class='subcomment-opinion'><a href='#' id='op" + resena.id + "'>Leer Mas...</a>" +
			  		"</p>";
			  if (!admin) {
				  resenasHTML += "<p class='subcomment-add'><a href='#' id='com" + resena.id + "'>Comentar</a>" +
			  		"</p>" +
			  		"</div>" +
			  		"<div class='form-block-2 w-form'>" +
			  		"<form action='javascript:void(0);' class='form-subcomment w-clearfix' data-name='Email Form 2' id='email-form-2' name='email-form-2'>" +
			  		"<textarea class='w-input' maxlength='5000' name='field' placeholder='Comentar' required='required'></textarea>" +
			  		"<input class='comment-button w-button' data-wait='Please wait...' type='submit' value='Comentar'>" +
			  		"</form>" +
			  		"</div";
			  }
			  		
			  resenasHTML += "</div>" +
		  		"</li>";
			  
		  });
		  $('#comentarios').html(resenasHTML);
		  $('.form-block-2').hide();
		  
		  
		  $('.subcomment-opinion > a').click(function(){
			  $('.subcomentarios-lista').hide();
			  resenaId = $(this).attr('id').replace('op','');
			  $(this).hide();
			  var direccionLeerSubcomentarios = "../api/1.0.0/interfaz/proyecto/leer-subcomentarios/";
			  var parametrosLeerSubcomentarios = {id: resenaId};
			  $.post(direccionLeerSubcomentarios, parametrosLeerSubcomentarios, leerSubcomentariosRespondio,"json").fail(leerSubcomentariosError);
		  });
		  $('.subcomment-add > a').click(function(){
			  $('.form-block-2').hide();
			  $(this).parent().parent().parent().children('.form-block-2').show();
			  resenaId = $(this).attr('id').replace('com','');
		  });
		  $('.form-subcomment').submit(function tokenizar(event){
			  var direccionEnviarSubcomentario = "../api/1.0.0/interfaz/proyecto/enviar-subcomentario/";
			  var subcomentario = $('.form-subcomment > .w-input').val();
			  var parametrosSubcomentario = {subcomentario: subcomentario, token: token, id: resenaId};
			  $.post(direccionEnviarSubcomentario, parametrosSubcomentario, enviarSubcomentarioRespondio,"json").fail(enviarSubcomentarioError);
		  });
	  }
	  
	  
	  var enviarSubcomentarioRespondio = function (datos){
		  console.log(datos);
	        if(datos.status == "ok"){
	        	$('.form-block-2').hide();
	        } else{

	        }
	  }
	  
	  var enviarSubcomentarioError = function (datos) {
		  console.log(datos);
	  }
	  
	  
	  var leerSubcomentariosRespondio = function (datos){
		  console.log(datos);
	        if(datos.status == "ok"){
	        	llenarSubcomentarios(datos.subcomentarios);
	        } else{

	        }
	  }
	  
	  var leerSubcomentariosError = function (datos) {
		  console.log(datos);
	  }
	  
	  
	  function llenarSubcomentarios(subcomentarios)
	  {
		  var subcomentariosHTML = "";
		  $.each(subcomentarios, function(index, subcomentario) {
			  subcomentariosHTML += "<li>" +
			  		"<div class='sub-comment w-clearfix'>" +
			  		"<h6 class='subcomment-user'>@" + subcomentario.nombreDeUsuario + "</h6>" +
			  		"<p class='subcomment-opinion'>" + subcomentario.comentario + "</p>" +
			  		"</div>" +
			  		"</li>";
			  
		  });
		  $("#sub"+resenaId).html(subcomentariosHTML);
		  $(".subcomentarios-lista").hide();
		  $("#sub"+resenaId).show();
	  }
	  
	  
	  var enviarResenaRespondio = function (datos){
		  console.log(datos);
	        if(datos.status == "ok"){
	        	$.post(direccionLeerResenas,parametrosResena, leerResenaRespondio,"json").fail(leerResenaError);
	        	$('#enviar-resena').hide();
	        } else{

	        }
	  }
	  
	  var enviarResenaError = function (datos) {
		  console.log(datos);
	  }
	  
	  $('#forma-calificar').submit(function (event){
		  $('#forma-boton-calificar').prop('value', 'Enviando...');
		  
		  if (enviandoCalificacion) {
			  console.log("regresa");
			  return;
		  }
		  enviandoCalificacion = true;
		  
		  var titulo = $('#titulo-comentario').val();
		  var comentario = $('#comentario').val();
		  
		  var parametros = {token: token, idDream: id, titulo: titulo, comentario: comentario, calificacion: calificacion};
		  $.post(direccionEnviarResena, parametros, enviarResenaRespondio, "json").fail(enviarResenaError);
	  });
	  
	  
	  $('#calificacion1').click(function(){
		  calificacion = 1;
		  $('#calificacion1').prop('src','../images/star.png');
		  $('#calificacion1').prop('srcset','../images/star-p-500.png 500w, ../images/star-p-800.png 800w, ../images/star-p-1080.png 1080w, ../images/star.png 1211w');
		  $('#calificacion2').prop('src','../images/star-unset.png');
		  $('#calificacion2').prop('srcset','../images/star-unset-p-500.png 500w, ../images/star-unset-p-800.png 800w, ../images/star-unset-p-1080.png 1080w, ../images/star-unset.png 1211w');
		  $('#calificacion3').prop('src','../images/star-unset.png');
		  $('#calificacion3').prop('srcset','../images/star-unset-p-500.png 500w, ../images/star-unset-p-800.png 800w, ../images/star-unset-p-1080.png 1080w, ../images/star-unset.png 1211w');
		  $('#calificacion4').prop('src','../images/star-unset.png');
		  $('#calificacion4').prop('srcset','../images/star-unset-p-500.png 500w, ../images/star-unset-p-800.png 800w, ../images/star-unset-p-1080.png 1080w, ../images/star-unset.png 1211w');
		  $('#calificacion5').prop('src','../images/star-unset.png');
		  $('#calificacion5').prop('srcset','../images/star-unset-p-500.png 500w, ../images/star-unset-p-800.png 800w, ../images/star-unset-p-1080.png 1080w, ../images/star-unset.png 1211w');
	  });
	  $('#calificacion2').click(function(){
		  calificacion = 2;
		  $('#calificacion1').prop('src','../images/star.png');
		  $('#calificacion1').prop('srcset','../images/star-p-500.png 500w, ../images/star-p-800.png 800w, ../images/star-p-1080.png 1080w, ../images/star.png 1211w');
		  $('#calificacion2').prop('src','../images/star.png');
		  $('#calificacion2').prop('srcset','../images/star-p-500.png 500w, ../images/star-p-800.png 800w, ../images/star-p-1080.png 1080w, ../images/star.png 1211w');
		  $('#calificacion3').prop('src','../images/star-unset.png');
		  $('#calificacion3').prop('srcset','../images/star-unset-p-500.png 500w, ../images/star-unset-p-800.png 800w, ../images/star-unset-p-1080.png 1080w, ../images/star-unset.png 1211w');
		  $('#calificacion4').prop('src','../images/star-unset.png');
		  $('#calificacion4').prop('srcset','../images/star-unset-p-500.png 500w, ../images/star-unset-p-800.png 800w, ../images/star-unset-p-1080.png 1080w, ../images/star-unset.png 1211w');
		  $('#calificacion5').prop('src','../images/star-unset.png');
		  $('#calificacion5').prop('srcset','../images/star-unset-p-500.png 500w, ../images/star-unset-p-800.png 800w, ../images/star-unset-p-1080.png 1080w, ../images/star-unset.png 1211w');
	  });
	  $('#calificacion3').click(function(){
		  calificacion = 3;
		  $('#calificacion1').prop('src','../images/star.png');
		  $('#calificacion1').prop('srcset','../images/star-p-500.png 500w, ../images/star-p-800.png 800w, ../images/star-p-1080.png 1080w, ../images/star.png 1211w');
		  $('#calificacion2').prop('src','../images/star.png');
		  $('#calificacion2').prop('srcset','../images/star-p-500.png 500w, ../images/star-p-800.png 800w, ../images/star-p-1080.png 1080w, ../images/star.png 1211w');
		  $('#calificacion3').prop('src','../images/star.png');
		  $('#calificacion3').prop('srcset','../images/star-p-500.png 500w, ../images/star-p-800.png 800w, ../images/star-p-1080.png 1080w, ../images/star.png 1211w');
		  $('#calificacion4').prop('src','../images/star-unset.png');
		  $('#calificacion4').prop('srcset','../images/star-unset-p-500.png 500w, ../images/star-unset-p-800.png 800w, ../images/star-unset-p-1080.png 1080w, ../images/star-unset.png 1211w');
		  $('#calificacion5').prop('src','../images/star-unset.png');
		  $('#calificacion5').prop('srcset','../images/star-unset-p-500.png 500w, ../images/star-unset-p-800.png 800w, ../images/star-unset-p-1080.png 1080w, ../images/star-unset.png 1211w');
	  });
	  $('#calificacion4').click(function(){
		  calificacion = 4;
		  $('#calificacion1').prop('src','../images/star.png');
		  $('#calificacion1').prop('srcset','../images/star-p-500.png 500w, ../images/star-p-800.png 800w, ../images/star-p-1080.png 1080w, ../images/star.png 1211w');
		  $('#calificacion2').prop('src','../images/star.png');
		  $('#calificacion2').prop('srcset','../images/star-p-500.png 500w, ../images/star-p-800.png 800w, ../images/star-p-1080.png 1080w, ../images/star.png 1211w');
		  $('#calificacion3').prop('src','../images/star.png');
		  $('#calificacion3').prop('srcset','../images/star-p-500.png 500w, ../images/star-p-800.png 800w, ../images/star-p-1080.png 1080w, ../images/star.png 1211w');
		  $('#calificacion4').prop('src','../images/star.png');
		  $('#calificacion4').prop('srcset','../images/star-p-500.png 500w, ../images/star-p-800.png 800w, ../images/star-p-1080.png 1080w, ../images/star.png 1211w');
		  $('#calificacion5').prop('src','../images/star-unset.png');
		  $('#calificacion5').prop('srcset','../images/star-unset-p-500.png 500w, ../images/star-unset-p-800.png 800w, ../images/star-unset-p-1080.png 1080w, ../images/star-unset.png 1211w');
	  });
	  $('#calificacion5').click(function(){
		  calificacion = 5;
		  $('#calificacion1').prop('src','../images/star.png');
		  $('#calificacion1').prop('srcset','../images/star-p-500.png 500w, ../images/star-p-800.png 800w, ../images/star-p-1080.png 1080w, ../images/star.png 1211w');
		  $('#calificacion2').prop('src','../images/star.png');
		  $('#calificacion2').prop('srcset','../images/star-p-500.png 500w, ../images/star-p-800.png 800w, ../images/star-p-1080.png 1080w, ../images/star.png 1211w');
		  $('#calificacion3').prop('src','../images/star.png');
		  $('#calificacion3').prop('srcset','../images/star-p-500.png 500w, ../images/star-p-800.png 800w, ../images/star-p-1080.png 1080w, ../images/star.png 1211w');
		  $('#calificacion4').prop('src','../images/star.png');
		  $('#calificacion4').prop('srcset','../images/star-p-500.png 500w, ../images/star-p-800.png 800w, ../images/star-p-1080.png 1080w, ../images/star.png 1211w');
		  $('#calificacion5').prop('src','../images/star.png');
		  $('#calificacion5').prop('srcset','../images/star-p-500.png 500w, ../images/star-p-800.png 800w, ../images/star-p-1080.png 1080w, ../images/star.png 1211w');
	  });

	  
	  function leerToken(nombre){
		  if (typeof(Storage) !== "undefined") {
			  //HTML5 Web Storage
			  return localStorage.getItem(nombre);
			} else {
				// Save as Cookie
				return leerCookie(nombre + "dreamcloud");
			}
	  }
	  
	  function leerCookie(cname) {
		    var name = cname + "=";
		    var decodedCookie = decodeURIComponent(document.cookie);
		    var ca = decodedCookie.split(';');
		    for(var i = 0; i <ca.length; i++) {
		        var c = ca[i];
		        while (c.charAt(0) == ' ') {
		            c = c.substring(1);
		        }
		        if (c.indexOf(name) == 0) {
		            return c.substring(name.length, c.length);
		        }
		    }
		    return "";
		}
	  
  });
})(jQuery);