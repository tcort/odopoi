function getTop(bound)
{
  var PI = 3.14159265358979323846;
  return(Math.atan( Math.exp( (bound.top *180 / 20037508.34) / 180 * PI))/PI *360 -90);
}


function getBottom(bound)
{
  var PI = 3.14159265358979323846;
  return(Math.atan( Math.exp( (bound.bottom *180 / 20037508.34) / 180 * PI))/PI *360 -90);
}

function getLeft(bound)
{
   return( bound.left *180 / 20037508.34);
}

function getRight(bound)
{
   return( bound.right *180 / 20037508.34);
}
