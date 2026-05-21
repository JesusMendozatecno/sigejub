Set WshShell = CreateObject("WScript.Shell")

' Verificar si ya hay un servidor corriendo en el puerto 8000
Set netstat = WshShell.Exec("cmd /c netstat -an | find "" :8000""")
Do While netstat.Status = 0
    WScript.Sleep 100
Loop

If netstat.StdOut.ReadAll <> "" Then
    ' Ya esta corriendo, abrir navegador
    WshShell.Run "start http://localhost:8000", 1, false
    WScript.Quit
End If

' Verificar que existe .env
Set fs = CreateObject("Scripting.FileSystemObject")
If Not fs.FileExists(".env") Then
    fs.CopyFile ".env.example", ".env"
    WshShell.Run "php artisan key:generate --force", 0, true
End If

' Iniciar servidor y abrir navegador
WshShell.Run "start http://localhost:8000", 1, false
WshShell.Run "php artisan serve --port=8000", 0, false
