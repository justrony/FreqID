<div>
    <form action="{{ route('login') }}" method="POST">
        @csrf
        <h1>Login</h1>

        <input type="email" name="email" placeholder="email" value="{{ old('email') }}">
        @error('email')
        <span style="color: red; display: block;">{{ $message }}</span>
        @enderror

        <input type="password" name="password" placeholder="senha">
        @error('password')
        <span style="color: red; display: block;">{{ $message }}</span>
        @enderror

        <button type="submit">Entrar</button>
    </form>
</div>
