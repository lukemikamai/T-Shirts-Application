function fadeIn(obj) {

   if ( typeof Animation == 'function' ) {
      Animation(document.getElementById(obj)).duration(400).checkpoint().to('opacity',1).from(0).by('height', '0px').duration(400).ease(Animation.ease.both).go(); 
   }
   else {
      setTimeout("fadeIn('"+obj+"')", 1000);
   }
}

if ( typeof fadeIn == 'function' ) {
	fadeIn('ttable');
}
else
{
	setTimeout("fadeIn('ttable')", 1000);
}
