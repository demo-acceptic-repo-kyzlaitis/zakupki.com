@section('title', 'Електронний майданчик учасник системи публічних закупівель.')
<head>
    @if(isset($metaTags))
    <title>{{$metaTags['title']}}</title>
    <meta name="description" content="{{$metaTags['description']}}">
    <meta name="keywords" content="{{$metaTags['keywords']}}">
    @else
    <title>@yield('title')</title>
    <meta name="keywords" content="електронні закупівлі, тендери, zakupki, prozorro, публичні закупівлі, Тендерні Закупівлі">
    @endif
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->



    <link href="//fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
    <link href="/css/fileicons.css" rel="stylesheet">
    <!-- Bootstrap -->
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <link href="/bootstrap/css/bootstrap-datetimepicker.min.css" rel="stylesheet"/>
    <link href="/select2/select2.css" rel="stylesheet"/>
    <link href="/css/animate.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
    <link href="/jquery/ui/jquery-ui.min.css" rel="stylesheet">
    <link href="/jquery/ui/jquery-ui.theme.min.css" rel="stylesheet">
    <script src="/jquery/dist/jquery.min.js"></script>
    <link rel="stylesheet" href="{{asset('css/custom.css')}}">
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.0/css/bootstrap-toggle.min.css" rel="stylesheet">
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.0/js/bootstrap-toggle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css" />

    <script src="/js/jquery.countdown.js"></script>

    <!-- Google Analytics -->
    <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
    ga('create', 'UA-90752694-1', 'auto');
    ga('require', 'displayfeatures');
    
    </script>
<!-- end Google Analytics -->
<!-- woopra -->
    <script>
    !function(){var a,b,c,d=window,e=document,f=arguments,g="script",h=["config","track","trackForm","trackClick","identify","visit","push","call"],i=function(){var a,b=this,c=function(a){b[a]=function(){return b._e.push([a].concat(Array.prototype.slice.call(arguments,0))),b}};for(b._e=[],a=0;a<h.length;a++)c(h[a])};for(d.__woo=d.__woo||{},a=0;a<f.length;a++)d.__woo[f[a]]=d[f[a]]=d[f[a]]||new i;b=e.createElement(g),b.async=1,b.src="//static.woopra.com/js/w.js",c=e.getElementsByTagName(g)[0],c.parentNode.insertBefore(b,c)}("woopra");
     
    // configure tracker
    woopra.config({
     domain: "zakupki.ua"
    });
     
    // track pageview
    woopra.track();
    </script>
    <!-- end woopra -->
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

