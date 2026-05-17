<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <!-- Necesitamos los estilos y Lucide para que se vea bien -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body style="background: #f1f5f9; font-family: sans-serif; padding: 40px 20px;">

    <!-- Contenedor Único -->
    <div style="max-width: 900px; margin: 0 auto;">
        
        <!-- Botón Volver y Título -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 style="color: #1a365d;">Mi Perfil</h1>
            <a href="{{ route('dashboard') }}" style="text-decoration: none; background: white; padding: 10px 20px; border-radius: 8px; color: #1a365d; font-weight: bold; border: 1px solid #cbd5e1;">
                ← Volver al Dashboard
            </a>
        </div>

        <!-- El Formulario de Perfil -->
        <form action="{{ route('usuarios.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 300px 1fr; gap: 30px;">
                
                <!-- Tarjeta Izquierda (Imagen) -->
                <div style="background: white; padding: 30px; border-radius: 12px; border-left: 6px solid #1a365d; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="width: 150px; height: 150px; border-radius: 50%; background: #f8fafc; margin: 0 auto 15px; overflow: hidden; border: 4px solid #f1f5f9; display: flex; align-items: center; justify-content: center;">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" style="width:100%; height:100%; object-fit:cover;">
                            @else
                                <i data-lucide="user" size="48" style="color: #cbd5e1;"></i>
                            @endif
                        </div>
                        <label for="avatar" style="cursor: pointer; background: #eff6ff; color: #1e3a8a; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                             📷 Cambiar Foto
                        </label>
                        <input type="file" name="avatar" id="avatar" hidden accept="image/*">
                    </div>
                    
                    <div style="background: #f1f5f9; padding: 12px; border-radius: 8px; text-align: center;">
                        <span style="font-size: 10px; font-weight: 900; color: #94a3b8; display: block;">ROL</span>
                        <strong style="color: #475569;">{{ $user->role ?? 'Usuario' }}</strong>
                    </div>
                </div>

                <!-- Tarjeta Derecha (Datos) -->
                <div style="background: white; padding: 30px; border-radius: 12px; border-left: 6px solid #064e3b; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label style="display:block; font-size: 11px; font-weight: 800; color: #64748b; margin-bottom: 5px;">NOMBRE</label>
                            <input type="text" name="name" value="{{ $user->name }}" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc;">
                        </div>
                        <div>
                            <label style="display:block; font-size: 11px; font-weight: 800; color: #64748b; margin-bottom: 5px;">EMAIL</label>
                            <input type="email" name="email" value="{{ $user->email }}" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc;">
                        </div>
                        <div style="grid-column: span 2;">
                            <label style="display:block; font-size: 11px; font-weight: 800; color: #64748b; margin-bottom: 5px;">NUEVA CONTRASEÑA (OPCIONAL)</label>
                            <input type="password" name="password" placeholder="••••••••" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc;">
                        </div>
                    </div>

                    <div style="margin-top: 30px; display: flex; justify-content: flex-end;">
                        <button type="submit" style="background: #1e3a8a; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: bold; cursor: pointer;">
                            Guardar Cambios
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>