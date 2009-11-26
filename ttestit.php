<div id="mydiv" style="opacity: 0; -moz-opacity: 0; filter: alpha(opacity=0);" >This is some text
</div>

<script>
function fadeIn(obj) {

   if ( typeof Animation == 'function' ) {
      Animation(document.getElementById(obj)).to('opacity',1).from(0).by('height', '0px').duration(2000).go(); 
   }
   else {
      setTimeout("fadeIn('"+obj+"')", 1000);
   }
}

if ( typeof fadeIn == 'function' ) {
	fadeIn('mydiv');
}
else
{
	setTimeout("fadeIn('mydiv')", 1000);
}
</script>
