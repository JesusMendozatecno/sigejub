Set ws = CreateObject("WScript.Shell")
ws.Run """" & CreateObject("Scripting.FileSystemObject").GetParentFolderName(WScript.ScriptFullName) & "\start.bat""", 0, False
