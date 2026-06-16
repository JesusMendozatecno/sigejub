Set WshShell = CreateObject("WScript.Shell")

' Leer puerto desde .env (default 8080)
Set fs = CreateObject("Scripting.FileSystemObject")
port = "8080"
If fs.FileExists(".env") Then
    Set envFile = fs.OpenTextFile(".env", 1)
    Do While Not envFile.AtEndOfStream
        line = Trim(envFile.ReadLine())
        If InStr(line, "APP_PORT=") = 1 Then
            port = Mid(line, InStr(line, "=") + 1)
            Exit Do
        End If
    Loop
    envFile.Close
End If

' Verificar si ya hay un servidor corriendo en el puerto
Set netstat = WshShell.Exec("cmd /c netstat -an | find "" :" & port & """")
Do While netstat.Status = 0
    WScript.Sleep 100
Loop

If netstat.StdOut.ReadAll <> "" Then
    ' Ya esta corriendo, abrir navegador
    WshShell.Run "start http://localhost:" & port, 1, false
    WScript.Quit
End If

' Verificar que existe .env
If Not fs.FileExists(".env") Then
    fs.CopyFile ".env.example", ".env"
    WshShell.Run "php artisan key:generate --force", 0, true
End If

' Iniciar servidor y abrir navegador
WshShell.Run "start http://localhost:" & port, 1, false
WshShell.Run "php artisan serve --port=" & port, 0, false
