<!DOCTYPE html>
<html lang="en">
@include('partials.common.header')

<body>

<div style="">

    @yield('content')
</div>

<footer class="footer">
    Zakupki.com.ua &copy; {{date('Y')}}
</footer>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="/jquery/ui/jquery-ui.min.js"></script>
<script src="/jquery/dist/jquery.mask.min.js"></script>
<script src="/jquery/dist/jquery.number.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/bootstrap/js/bootstrap.min.js"></script>
<script src="/moment/moment-with-locales.min.js"></script>
<script src="/bootstrap/js/bootstrap-datetimepicker.min.js"></script>
<script src="/select2/select2.min.js"></script>

<script src="/js/setup.input.form.js"></script>
<script src="/js/jquery.countdown.js"></script
</body>
</html>


