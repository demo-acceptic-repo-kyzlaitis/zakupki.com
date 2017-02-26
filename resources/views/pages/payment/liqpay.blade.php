<script>
var opts = {
lines: 11 // The number of lines to draw
, length: 44 // The length of each line
, width: 7 // The line thickness
, radius: 25 // The radius of the inner circle
, scale: 1.25 // Scales overall size of the spinner
, corners: 1 // Corner roundness (0..1)
, color: '#000' // #rgb or #rrggbb or array of colors
, opacity: 0 // Opacity of the lines
, rotate: 71 // The rotation offset
, direction: 1 // 1: clockwise, -1: counterclockwise
, speed: 0.7 // Rounds per second
, trail: 100 // Afterglow percentage
, fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
, zIndex: 2e9 // The z-index (defaults to 2000000000)
, className: 'spinner' // The CSS class to assign to the spinner
, top: '50%' // Top position relative to parent
, left: '50%' // Left position relative to parent
, shadow: true // Whether to render a shadow
, hwaccel: true // Whether to use hardware acceleration
, position: 'absolute' // Element positioning
}
var target = document.getElementById('foo')
var spinner = new Spinner(opts).spin(target);
</script>
<script>
        var spinner = new Spinner().spin()
        target.appendChild(spinner.el)
</script>
<div style="display:none">
<form method="POST" action="https://www.liqpay.com/api/3/checkout"
      accept-charset="utf-8" id="liqpay">
        <input type="hidden" name="data" value="{{$data}}"/>
        <input type="hidden" name="signature" value="{{$signature}}"/>
        <input type="image"
               src="//static.liqpay.com/buttons/p1ru.radius.png"/>
</form>
        </div>
<script>
        document.getElementById('liqpay').submit();
</script>