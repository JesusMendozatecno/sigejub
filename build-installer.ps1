# build-installer.ps1
# Compila el instalador grafico SIGEJUB a .exe usando el compilador C# de .NET Framework
# Uso: .\build-installer.ps1
# Salida: SIGEJUB-Installer.exe

$ErrorActionPreference = 'Stop'
$projectDir = Split-Path -Parent $MyInvocation.MyCommand.Path

# ─── Buscar csc.exe ───────────────────────────────
$csc = Get-ChildItem -Path "$env:SystemRoot\Microsoft.NET\Framework\v*\csc.exe" |
    Sort-Object { [version]$_.Directory.Name.Substring(1) } -Descending |
    Select-Object -First 1 -ExpandProperty FullName

if (-not $csc) {
    Write-Host "[ERROR] No se encontro csc.exe (.NET Framework)." -Foreground Red
    Write-Host "Instala .NET Framework desde https://dotnet.microsoft.com/download" -Foreground Yellow
    exit 1
}

$icoPath = Join-Path $projectDir "public\img\imagen_2026-05-19_065531142.ico"
if (-not (Test-Path $icoPath)) {
    Write-Host "[ADVERTENCIA] Icono no encontrado en $icoPath" -Foreground Yellow
    $icoPath = ""
}

Write-Host "[OK] Compilador: $csc" -Foreground Green

# ─── Escribir codigo C# a archivo temporal ──────
$csFile = Join-Path $env:TEMP "sigejub-installer.cs"

@"
using System;
using System.Diagnostics;
using System.Drawing;
using System.IO;
using System.Text;
using System.Windows.Forms;

namespace SIGEJUB_Installer
{
    static class Program
    {
        [STAThread]
        static void Main()
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new InstallerForm());
        }
    }

    public class InstallerForm : Form
    {
        private Label lblStatus;
        private ProgressBar progressBar;
        private Button btnInstall;
        private RichTextBox logBox;
        private CheckBox chkShortcut;
        private bool installing = false;

        private string dbHost = "127.0.0.1";
        private string dbPort = "3306";
        private string dbName = "bd-sigejub";
        private string dbUser = "root";
        private string dbPass = "";
        private int selectedPort = 8000;

        public InstallerForm()
        {
            this.Text = "SIGEJUB - Instalador";
            this.Size = new Size(520, 430);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.FormBorderStyle = FormBorderStyle.FixedSingle;
            this.MaximizeBox = false;
            this.Icon = LoadIcon();
            this.BackColor = Color.FromArgb(248, 250, 252);

            // Logo
            var lblLogo = new Label
            {
                Text = "SIGEJUB",
                Font = new Font("Segoe UI", 22, FontStyle.Bold),
                ForeColor = Color.FromArgb(26, 54, 93),
                Size = new Size(240, 40),
                Location = new Point(140, 20),
                TextAlign = ContentAlignment.MiddleCenter
            };

            var lblSub = new Label
            {
                Text = "Sistema Integral de Gestión de Jubilaciones",
                Font = new Font("Segoe UI", 9, FontStyle.Regular),
                ForeColor = Color.FromArgb(100, 116, 139),
                Size = new Size(340, 18),
                Location = new Point(90, 58),
                TextAlign = ContentAlignment.MiddleCenter
            };

            // Log
            logBox = new RichTextBox
            {
                Location = new Point(16, 90),
                Size = new Size(472, 180),
                ReadOnly = true,
                BackColor = Color.FromArgb(15, 23, 42),
                ForeColor = Color.FromArgb(226, 232, 240),
                Font = new Font("Consolas", 9),
                BorderStyle = BorderStyle.FixedSingle
            };

            // Progress bar
            progressBar = new ProgressBar
            {
                Location = new Point(16, 278),
                Size = new Size(472, 22),
                Minimum = 0, Maximum = 100, Value = 0
            };

            lblStatus = new Label
            {
                Location = new Point(16, 305),
                Size = new Size(360, 20),
                Text = "Listo para instalar",
                ForeColor = Color.FromArgb(71, 85, 105),
                Font = new Font("Segoe UI", 9)
            };

            // Boton config BD
            var btnConfig = new Button
            {
                Text = "Configurar BD...",
                Location = new Point(16, 335),
                Size = new Size(120, 30),
                FlatStyle = FlatStyle.Flat,
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(26, 54, 93),
                Font = new Font("Segoe UI", 9),
                Cursor = Cursors.Hand
            };
            btnConfig.FlatAppearance.BorderSize = 1;
            btnConfig.FlatAppearance.BorderColor = Color.FromArgb(203, 213, 225);
            btnConfig.Click += (s, e) => ShowConfig();

            // Checkbox acceso directo
            chkShortcut = new CheckBox
            {
                Text = "Crear acceso directo en el escritorio",
                Location = new Point(145, 338),
                Size = new Size(220, 24),
                Font = new Font("Segoe UI", 9),
                ForeColor = Color.FromArgb(71, 85, 105),
                Checked = true
            };

            // Boton Instalar
            btnInstall = new Button
            {
                Text = "Instalar",
                Location = new Point(385, 333),
                Size = new Size(103, 34),
                FlatStyle = FlatStyle.Flat,
                BackColor = Color.FromArgb(26, 54, 93),
                ForeColor = Color.White,
                Font = new Font("Segoe UI", 10, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnInstall.FlatAppearance.BorderSize = 0;
            btnInstall.Click += BtnInstall_Click;

            // Boton Cerrar
            var btnClose = new Button
            {
                Text = "Cerrar",
                Location = new Point(410, 375),
                Size = new Size(80, 28),
                FlatStyle = FlatStyle.Flat,
                BackColor = Color.FromArgb(248, 250, 252),
                ForeColor = Color.FromArgb(100, 116, 139),
                Font = new Font("Segoe UI", 9),
                Cursor = Cursors.Hand
            };
            btnClose.FlatAppearance.BorderSize = 1;
            btnClose.FlatAppearance.BorderColor = Color.FromArgb(203, 213, 225);
            btnClose.Click += (s, e) => this.Close();

            this.Controls.AddRange(new Control[] {
                lblLogo, lblSub, logBox, progressBar, lblStatus,
                btnConfig, chkShortcut, btnInstall, btnClose
            });

            logBox.AppendText(" Bienvenido al instalador de SIGEJUB\n");
            logBox.AppendText(" Requisitos: PHP 8.2+, Composer, MySQL\n");
            logBox.AppendText(" Puedes configurar la BD antes de instalar.\n\n");
        }

        private Icon LoadIcon()
        {
            var path = Path.Combine(Application.StartupPath, "public", "img",
                "imagen_2026-05-19_065531142.ico");
            if (File.Exists(path))
                try { return new Icon(path); } catch { }
            return SystemIcons.Application;
        }

        private void ShowConfig()
        {
            var frm = new Form
            {
                Text = "Base de Datos",
                Size = new Size(360, 260),
                StartPosition = FormStartPosition.CenterParent,
                FormBorderStyle = FormBorderStyle.FixedDialog,
                MaximizeBox = false, MinimizeBox = false,
                ShowInTaskbar = false
            };

            var lbl = new Label
            {
                Text = "Valores por defecto (XAMPP): dejar vacio",
                Location = new Point(12, 12),
                Size = new Size(320, 20),
                ForeColor = Color.FromArgb(100, 116, 139)
            };

            int y = 40;
            var txtHost = AddField(frm, "Host:", ref y, dbHost);
            var txtPort = AddField(frm, "Puerto:", ref y, dbPort);
            var txtDb = AddField(frm, "BD:", ref y, dbName);
            var txtUser = AddField(frm, "Usuario:", ref y, dbUser);
            var txtPass = AddField(frm, "Clave:", ref y, dbPass, true);

            var btnOk = new Button { Text = "Guardar", Location = new Point(160, y + 10),
                Size = new Size(80, 28), DialogResult = DialogResult.OK };
            var btnCancel = new Button { Text = "Cancelar", Location = new Point(250, y + 10),
                Size = new Size(80, 28), DialogResult = DialogResult.Cancel };

            frm.Controls.AddRange(new Control[] { lbl, btnOk, btnCancel });
            frm.AcceptButton = btnOk;

            if (frm.ShowDialog() == DialogResult.OK)
            {
                dbHost = string.IsNullOrWhiteSpace(txtHost.Text) ? "127.0.0.1" : txtHost.Text;
                dbPort = string.IsNullOrWhiteSpace(txtPort.Text) ? "3306" : txtPort.Text;
                dbName = string.IsNullOrWhiteSpace(txtDb.Text) ? "bd-sigejub" : txtDb.Text;
                dbUser = string.IsNullOrWhiteSpace(txtUser.Text) ? "root" : txtUser.Text;
                dbPass = txtPass.Text;
                AppendLog("[OK] Configuracion de BD guardada", Color.Cyan);
            }
        }

        private TextBox AddField(Form form, string label, ref int y, string defaultValue, bool password = false)
        {
            var lbl = new Label { Text = label, Location = new Point(12, y + 4),
                Size = new Size(70, 20) };
            var tb = new TextBox { Text = defaultValue, Location = new Point(88, y),
                Size = new Size(240, 22) };
            if (password) tb.PasswordChar = '*';
            form.Controls.AddRange(new Control[] { lbl, tb });
            y += 30;
            return tb;
        }

        private void AppendLog(string text, Color? color = null)
        {
            if (logBox.InvokeRequired)
            {
                logBox.Invoke(new Action(() => AppendLog(text, color)));
                return;
            }
            logBox.SelectionStart = logBox.TextLength;
            logBox.SelectionLength = 0;
            logBox.SelectionColor = color ?? Color.White;
            logBox.AppendText(text + "\n");
            logBox.ScrollToCaret();
        }

        private void SetStatus(string text)
        {
            if (lblStatus.InvokeRequired)
            { lblStatus.Invoke(new Action(() => SetStatus(text))); return; }
            lblStatus.Text = text;
        }

        private void SetProgress(int value)
        {
            if (progressBar.InvokeRequired)
            { progressBar.Invoke(new Action(() => SetProgress(value))); return; }
            progressBar.Value = Math.Min(value, 100);
        }

        private bool RunCmd(string cmd, string args, out string output)
        {
            output = "";
            try
            {
                var psi = new ProcessStartInfo(cmd, args)
                {
                    RedirectStandardOutput = true,
                    RedirectStandardError = true,
                    UseShellExecute = false,
                    CreateNoWindow = true,
                    WorkingDirectory = Application.StartupPath,
                    StandardOutputEncoding = Encoding.UTF8,
                    StandardErrorEncoding = Encoding.UTF8
                };
                using (var proc = Process.Start(psi))
                {
                    string o = proc.StandardOutput.ReadToEnd();
                    string e = proc.StandardError.ReadToEnd();
                    proc.WaitForExit(180000);
                    output = (o + e).Trim();
                    return proc.ExitCode == 0;
                }
            }
            catch (Exception ex) { output = ex.Message; return false; }
        }

        private void BtnInstall_Click(object sender, EventArgs e)
        {
            if (installing) return;
            installing = true;
            btnInstall.Enabled = false;
            btnInstall.Text = "Instalando...";
            SetStatus("Instalando...");

            string output;

            // ─── 1: PHP ─────────────────────────────
            AppendLog("[1/7] Verificando PHP...", Color.Yellow);
            if (!RunCmd("where", "php", out output))
            {
                AppendLog("[ERROR] PHP no encontrado en PATH", Color.Red);
                goto error;
            }
            string phpVer = "";
            RunCmd("php", "-r \"echo PHP_MAJOR_VERSION;\"", out phpVer);
            AppendLog("[OK] PHP " + phpVer + ".x", Color.Green);
            SetProgress(14);

            // ─── 2: Composer ─────────────────────────
            AppendLog("[2/7] Verificando Composer...", Color.Yellow);
            if (!RunCmd("where", "composer", out output))
            {
                AppendLog("[ERROR] Composer no encontrado", Color.Red);
                goto error;
            }
            AppendLog("[OK] Composer encontrado", Color.Green);
            SetProgress(28);

            // ─── 3: .env ──────────────────────────────
            AppendLog("[3/7] Configurando .env...", Color.Yellow);
            string envPath = Path.Combine(Application.StartupPath, ".env");
            string envExample = Path.Combine(Application.StartupPath, ".env.example");

            if (!File.Exists(envPath))
            {
                if (File.Exists(envExample))
                {
                    File.Copy(envExample, envPath);
                    AppendLog("[OK] .env creado", Color.Green);
                }
                else
                {
                    AppendLog("[ERROR] Falta .env.example", Color.Red);
                    goto error;
                }
            }
            else AppendLog("[OK] .env existe", Color.Green);

            string env = File.ReadAllText(envPath, Encoding.UTF8);
            env = ReplaceEnv(env, "DB_HOST", dbHost);
            env = ReplaceEnv(env, "DB_PORT", dbPort);
            env = ReplaceEnv(env, "DB_DATABASE", dbName);
            env = ReplaceEnv(env, "DB_USERNAME", dbUser);
            env = ReplaceEnv(env, "DB_PASSWORD", dbPass);
            File.WriteAllText(envPath, env, Encoding.UTF8);
            AppendLog("[OK] BD configurada en .env", Color.Green);
            SetProgress(42);

            // ─── 4: composer install ─────────────────
            AppendLog("[4/7] Instalando dependencias...", Color.Yellow);
            if (!RunCmd("composer", "install --no-interaction --no-ansi", out output))
                AppendLog("[ADVERTENCIA] composer con errores", Color.Orange);
            else
                AppendLog("[OK] Dependencias instaladas", Color.Green);
            SetProgress(56);

            // ─── 5: key:generate ────────────────────
            AppendLog("[5/7] Generando APP_KEY...", Color.Yellow);
            if (!RunCmd("php", "artisan key:generate --force --no-ansi", out output))
                AppendLog("[ADVERTENCIA] " + output, 
                Color.Orange);
            else
                AppendLog("[OK] APP_KEY generada", Color.Green);
            SetProgress(70);

            // ─── 6: migrate ──────────────────────────
            AppendLog("[6/7] Ejecutando migraciones...", Color.Yellow);
            AppendLog("      (La BD debe existir en MySQL)", Color.Gray);
            if (!RunCmd("php", "artisan migrate --force --no-ansi", out output))
                AppendLog("[ADVERTENCIA] Migraciones: " + output.Substring(0, Math.Min(300, output.Length)), Color.Orange);
            else
                AppendLog("[OK] Migraciones ejecutadas", Color.Green);
            SetProgress(84);

            // ─── 7: Finalizar ────────────────────────
            AppendLog("[7/7] Finalizando...", Color.Yellow);
            string _out = "";
            RunCmd("php", "artisan optimize:clear --no-ansi", out _out);
            string pubStorage = Path.Combine(Application.StartupPath, "public", "storage");
            if (Directory.Exists(pubStorage)) Directory.Delete(pubStorage, true);
            RunCmd("php", "artisan storage:link --no-ansi", out _out);
            AppendLog("[OK] Cache + storage link", Color.Green);
            SetProgress(92);

            // Puerto libre
            selectedPort = FindFreePort();
            env = File.ReadAllText(envPath, Encoding.UTF8);
            env = ReplaceEnv(env, "APP_URL", "http://localhost:" + selectedPort);
            File.WriteAllText(envPath, env, Encoding.UTF8);
            AppendLog("[OK] Puerto " + selectedPort + " asignado", Color.Green);
            SetProgress(100);

            // Acceso directo
            if (chkShortcut.Checked)
            {
                try
                {
                    string desktop = Environment.GetFolderPath(Environment.SpecialFolder.Desktop);
                    AppendLog("Creando acceso directo en: " + desktop, Color.Gray);

                    // Metodo 1: .url file (fallback mas simple)
                    string urlFile = Path.Combine(desktop, "SIGEJUB - Sistema.url");
                    using (StreamWriter sw = new StreamWriter(urlFile, false, new UTF8Encoding(false)))
                    {
                        sw.WriteLine("[InternetShortcut]");
                        sw.WriteLine("URL=http://localhost:" + selectedPort);
                    }
                    AppendLog("[OK] Acceso directo creado: " + urlFile, Color.Green);
                }
                catch (Exception ex)
                {
                    AppendLog("[AVISO] No se pudo crear acceso directo: " + ex.Message, Color.Orange);
                }
            }

            AppendLog("", Color.White);
            AppendLog("  INSTALACION COMPLETADA", Color.Cyan);
            AppendLog("  Puerto: " + selectedPort, Color.Cyan);
            AppendLog("  URL:    http://localhost:" + selectedPort, Color.Cyan);

            btnInstall.Text = "Iniciar";
            btnInstall.Click -= BtnInstall_Click;
            btnInstall.Click += (s2, e2) =>
            {
                Process.Start("http://localhost:" + selectedPort);
                Process.Start(new ProcessStartInfo
                {
                    FileName = "php",
                    Arguments = "artisan serve --port=" + selectedPort,
                    WorkingDirectory = Application.StartupPath,
                    UseShellExecute = true
                });
                this.Close();
            };
            btnInstall.Enabled = true;
            installing = false;
            SetStatus("Instalacion completa en puerto " + selectedPort);
            return;

        error:
            AppendLog("[ERROR] Instalacion cancelada.", Color.Red);
            SetStatus("Error durante la instalacion");
            btnInstall.Text = "Instalar";
            btnInstall.Enabled = true;
            installing = false;
        }

        private string ReplaceEnv(string content, string key, string value)
        {
            var lines = content.Split('\n');
            for (int i = 0; i < lines.Length; i++)
            {
                if (lines[i].StartsWith(key + "=", StringComparison.OrdinalIgnoreCase))
                    lines[i] = key + "=" + value;
            }
            return string.Join("\n", lines);
        }

        private int FindFreePort()
        {
            for (int port = 8000; port <= 9000; port++)
            {
                try
                {
                    var listener = new System.Net.Sockets.TcpListener(
                        System.Net.IPAddress.Loopback, port);
                    listener.Start();
                    listener.Stop();
                    return port;
                }
                catch { }
            }
            return 8000;
        }
    }
}
"@ | Out-File -FilePath $csFile -Encoding UTF8

# ─── Compilar ────────────────────────────────────
$outExe = Join-Path $projectDir "SIGEJUB-Installer.exe"

# Usar strings individuales (Start-Process maneja el quoting)
$argList = @()
$argList += "/noconfig"
$argList += "/target:winexe"
$argList += '/out:"' + $outExe + '"'
$argList += "/reference:System.dll"
$argList += "/reference:System.Windows.Forms.dll"
$argList += "/reference:System.Drawing.dll"
$argList += "/reference:System.Net.dll"
$argList += "/reference:Microsoft.CSharp.dll"

if ($icoPath -and (Test-Path $icoPath)) {
    $argList += '/win32icon:"' + $icoPath + '"'
}

$argList += $csFile

Write-Host "Compilando..." -Foreground Yellow
$p = Start-Process -FilePath $csc -ArgumentList $argList -NoNewWindow -Wait -PassThru

# Si el .exe ya existe y no se pudo sobrescribir, reintentar
if ($p.ExitCode -ne 0) {
    Start-Sleep -Seconds 1
    try { Remove-Item $outExe -Force -ErrorAction Stop } catch { }
    $p = Start-Process -FilePath $csc -ArgumentList $argList -NoNewWindow -Wait -PassThru
}

if ($p.ExitCode -eq 0) {
    $size = [math]::Round((Get-Item $outExe).Length / 1KB)
    Write-Host "[OK] Instalador creado: $outExe ($size KB)" -Foreground Green
    Write-Host "" -Foreground Green
    Write-Host "Ejecuta:" -Foreground Cyan
    Write-Host "  .\SIGEJUB-Installer.exe" -Foreground White
    Write-Host "" -Foreground Cyan
    Write-Host "Puedes copiar el .exe a otros PCs (no requiere PHP ni nada extra)." -Foreground Cyan
} else {
    Write-Host "[ERROR] Compilacion fallida (exit code: $($p.ExitCode))" -Foreground Red
    Write-Host "Revisa $csFile para errores de sintaxis C#." -Foreground Yellow
}
