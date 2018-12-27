<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>paypal</title>
</head>
<body>
<div class="container">
    <form action="{{route('create-payment')}}" method="post">
     @csrf
        <input type="submit" value="pay now" class="form-control">
    </form>
</div>
<div class="container">
    <form action="{{route('agrement','P-1MX539289Y167074R5ZAOPXY')}}" method="post">
     @csrf
        <input type="submit" value="Suscribe now" class="form-control">
    </form>
</div>
</body>
</html>
