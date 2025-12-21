<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name', 'Order Tracking System') }}</title>
            <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      background-color: #f5f5f5;
      color: #333;
    }
    h1 {
      font-size: 4rem;
      font-weight: 700;
      text-align: center;
      letter-spacing: -0.02em;
    }
    @media (max-width: 768px) {
      h1 {
        font-size: 2.5rem;
      }
    }
            </style>
    </head>
<body>
  <h1>{{ config('app.name', 'Order Tracking System') }}</h1>
    </body>
</html>
