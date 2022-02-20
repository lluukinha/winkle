<html>
  <body>
    <p>Olá {{ $user->name }}!</p>
    <p></p>
    <p>
        Foi feito um pedido para redefinição de senha para o seu email.<br>
        Se você realmente esqueceu sua senha, utilize o código <b>{{ $token }}</b> na tela de redefinição de senha ou <a href="https://winkle.app/redefine-password/{{$user->email}}/{{$token}}" target="_blank">clique aqui</a> para redefinir sua senha.
    </p>
    <p>Você tem 24 horas para redefinir sua senha com este código. Depois, ele ficará inutilizável.</p>
    <p></p>
    <p>Se não foi você, desconsidere este e-mail.</p>
    <p></p>
    <p>Att, <br>
    Lucas Souza, criador do Winkle!</p>
  </body>
</html>