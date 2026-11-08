' SIGEJUB - Lanzador del sistema.
' Inicia el servidor (si no estaba corriendo) y abre el navegador.
' Se muestra la ventana de progreso: si algo falla, el usuario ve el motivo
' en lugar de quedarse sin respuesta.
Option Explicit

Dim fso, sh, base, bat
Set fso = CreateObject("Scripting.FileSystemObject")
Set sh = CreateObject("WScript.Shell")

base = fso.GetParentFolderName(WScript.ScriptFullName)
bat = base & "\start.bat"

If Not fso.FileExists(bat) Then
    MsgBox "No se encontro el iniciador start.bat en:" & vbCrLf & vbCrLf & base & vbCrLf & vbCrLf & _
           "Reinstala SIGEJUB.", 16, "SIGEJUB"
    WScript.Quit 1
End If

sh.CurrentDirectory = base
sh.Run """" & bat & """", 1, False
WScript.Quit 0
