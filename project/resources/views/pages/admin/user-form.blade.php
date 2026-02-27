<form action="{{ route('user.store') }}" method="POST">
    @csrf

    <div style="margin-bottom: 10px;">
        <label for="name">Nome:</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required>

        @error('name')
        <span style="color: red; display: block; font-size: 14px;">{{ $message }}</span>
        @enderror
    </div>

    <div style="margin-bottom: 10px;">
        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required>

        @error('email')
        <span style="color: red; display: block; font-size: 14px;">{{ $message }}</span>
        @enderror
    </div>

    <button type="submit">Cadastrar Usuário</button>
</form>
