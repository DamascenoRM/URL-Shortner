<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Redirecionando . . .</title>
<!-- Adicione a ligação para o Bootstrap CSS -->
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Estilo para a animação */
    @keyframes bounce-in {
      0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
      }
      40% {
        transform: translateY(-20px);
      }
      60% {
        transform: translateY(-10px);
      }
    }

    /* Aplica o estilo da animação ao texto */
    .redirecionando-text {
      animation: bounce-in 1s infinite;
    }
  
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }

        h1 {
            color: #007bff;
        }

        form {
            max-width: 400px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        input[type="url"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: #007bff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
 <div class="container">
  <div class="d-flex justify-content-center">
    <!-- Adiciona a classe redirecionando-text ao texto para aplicar a animação -->
    <div class="redirecionando-text"><h2 class="alert alert-info" role="alert">Redirect . . .</h1></div>
  </div>
</div>
  <div id="google-analytics"></div>

  <div id="facebook-pixel"></div>

  <div id="adobe-analytics"></div>

  <div id="hotjar"></div>

  <div id="matomo"></div>


<!-- Adicione a ligação para o Bootstrap JS e o Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    // Carrega o Pixel do Google
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-XXXXXXXX-X', 'auto');
ga('send', 'pageview');

// Carrega o Pixel do Facebook
(function(f,b,e,v,n,t,s){if(f.fbq){return;}n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq){f._fbq=n;}
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');

fbq('init', '1234567890');
fbq('track', 'PageView');

// Carrega o Adobe Analytics
(function(a){a.ga=a.ga||function(){(a.ga.q=a.ga.q||[]).push(arguments)};a.ga.l=+new Date();
var b=document,c=b.createElement("script"),d=b.getElementsByTagName("script")[0];c.async=1;
c.src=("https:"==location.protocol?"https://ssl":"http://www")+".adobeanalytics.com/analytics.js";
d.parentNode.insertBefore(c,d)})(window);

window.adobeanalytics.setAccount("1234567890");
window.adobeanalytics.trackPageView();

// Carrega o Hotjar
(function(h,o,t,j,a,r){
h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
h._hjSettings={hjid:1234567890,hjsv:6};
a=o.createElement(t);r=o.getElementsByTagName(t)[0];
a.async=1;a.src=j;r.parentNode.insertBefore(a,r)}(window,document,'script','https://static.hotjar.com/c/hotjar-full.js'));

hj.hj();

// Carrega o Matomo
(function(i,s,o,g,r,a,m){i['MatomoObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1

</script>
</body>
</html>
