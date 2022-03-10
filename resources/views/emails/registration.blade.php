<html>
  <body>
    <p>Olá {{ $user->email }}!</p>
    <p></p>
    <p>Seu cadastro no <b>Winkle</b>, sistema de gerenciamento de senhas está quase finalizado!</p>
    <p></p>
    <p>Para finalizar seu cadastro, <a href="https://winkle.app/registration/{{$user->email}}/{{$token}}" target="_blank">clique aqui</a></p>
    <p></p>
    <p>
      Caso o link acima não funcione, copie e cole o link a seguir em seu navegador:<br>
      <b>https://winkle.app/registration/{{$user->email}}/{{$user->remember_token}}</b>
    </p>
    <p></p>
    <p>Terminando seu cadastro, você terá acesso a uma nova forma de agrupar suas senhas, sem precisar usar a mesma em todo website.</p>
    <p></p>
    <p>Seja muito bem vindo.</p>
    <p>
        Att, <br>
        <b>Lucas Souza</b>, criador do Winkle!
    </p>
  </body>
</html>