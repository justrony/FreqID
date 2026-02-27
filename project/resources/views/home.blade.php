Logou!

Bem-vindo {{ auth()->user()->name}}

<br>
<form action="{{route('logout')}}" method="POST">
    @csrf
    <button>Logout</button>
</form>

<a href="{{route('user.create')}}">Cadastrar Usuário</a>
