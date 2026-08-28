// ============================================================
// SIGEJUB - Instalador Profesional (C# / WinForms / .NET Framework)
// Diseño moderno basado en la identidad visual de la web SIGEJUB:
//   • Navy profundo  #0f172a → #1a365d
//   • Gradiente acento índigo #6366f1 → #4f46e5
//   • Acento violeta #7c3aed, éxito verde #16a34a
// ============================================================
using System;
using System.Diagnostics;
using System.Drawing;
using System.Drawing.Drawing2D;
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

    // ─── Paleta de marca (coherente con la web) ───────────────
    static class Palette
    {
        public static Color Navy        = FromHex("#0f172a");
        public static Color Navy2       = FromHex("#1a365d");
        public static Color Navy3       = FromHex("#1e293b");
        public static Color Indigo      = FromHex("#6366f1");
        public static Color IndigoDark  = FromHex("#4f46e5");
        public static Color IndigoLight = FromHex("#a5b4fc");
        public static Color Violet      = FromHex("#7c3aed");
        public static Color Green       = FromHex("#16a34a");
        public static Color Red         = FromHex("#dc2626");
        public static Color Slate100    = FromHex("#f1f5f9");
        public static Color Slate200    = FromHex("#e2e8f0");
        public static Color Slate400    = FromHex("#94a3b8");
        public static Color Slate500    = FromHex("#64748b");
        public static Color Slate600    = FromHex("#475569");
        public static Color Surface     = FromHex("#f6f7fb");
        public static Color White       = Color.White;

        public static Color FromHex(string hex)
        {
            hex = hex.TrimStart('#');
            int r = Convert.ToInt32(hex.Substring(0, 2), 16);
            int g = Convert.ToInt32(hex.Substring(2, 2), 16);
            int b = Convert.ToInt32(hex.Substring(4, 2), 16);
            return Color.FromArgb(r, g, b);
        }
    }

    // ─── Botón redondeado con hover (owner-drawn) ────────────
    class RoundedButton : Button
    {
        private bool hover = false;
        private bool pressed = false;

        public Color NormalBack { get; set; }
        public Color HoverBack  { get; set; }
        public Color Border     { get; set; }
        public int  Radius      { get; set; }
        public bool IsPrimary   { get; set; }

        public RoundedButton()
        {
            NormalBack = Palette.Indigo;
            HoverBack = Palette.IndigoDark;
            Border = Color.Transparent;
            Radius = 8;
            IsPrimary = true;
            SetStyle(ControlStyles.AllPaintingInWmPaint | ControlStyles.UserPaint |
                     ControlStyles.OptimizedDoubleBuffer | ControlStyles.ResizeRedraw, true);
            this.Font = new Font("Segoe UI", 9.5f, FontStyle.Regular);
            this.ForeColor = Palette.White;
            this.Size = new Size(120, 40);
            this.Cursor = Cursors.Hand;
        }

        protected override void OnMouseEnter(EventArgs e) { hover = true; Invalidate(); base.OnMouseEnter(e); }
        protected override void OnMouseLeave(EventArgs e) { hover = false; pressed = false; Invalidate(); base.OnMouseLeave(e); }
        protected override void OnMouseDown(MouseEventArgs e) { pressed = true; Invalidate(); base.OnMouseDown(e); }
        protected override void OnMouseUp(MouseEventArgs e) { pressed = false; Invalidate(); base.OnMouseUp(e); }

        protected override void OnPaint(PaintEventArgs e)
        {
            e.Graphics.SmoothingMode = SmoothingMode.AntiAlias;
            var r = ClientRectangle;
            r.Width--; r.Height--;

            Color back = hover ? (pressed ? Darken(HoverBack, 8) : HoverBack) : NormalBack;
            if (IsPrimary)
            {
                using (var path = RoundedRect(r, Radius))
                using (var gbrush = new LinearGradientBrush(r, back, Lighten(back, 6), 45f))
                    e.Graphics.FillPath(gbrush, path);
            }
            else
            {
                using (var path = RoundedRect(r, Radius))
                using (var b = new SolidBrush(back))
                    e.Graphics.FillPath(b, path);
            }

            if (Border != Color.Transparent)
            {
                using (var path = RoundedRect(r, Radius))
                using (var p = new Pen(Border, 1))
                    e.Graphics.DrawPath(p, path);
            }

            TextRenderer.DrawText(e.Graphics, this.Text, this.Font, ClientRectangle, this.ForeColor,
                TextFormatFlags.HorizontalCenter | TextFormatFlags.VerticalCenter);
        }

        public static GraphicsPath RoundedRect(Rectangle r, int radius)
        {
            int d = radius * 2;
            var path = new GraphicsPath();
            path.AddArc(r.X, r.Y, d, d, 180, 90);
            path.AddArc(r.Right - d, r.Y, d, d, 270, 90);
            path.AddArc(r.Right - d, r.Bottom - d, d, d, 0, 90);
            path.AddArc(r.X, r.Bottom - d, d, d, 90, 90);
            path.CloseFigure();
            return path;
        }

        public static Color Lighten(Color c, int pct)
        {
            int p = pct;
            return Color.FromArgb(
                Math.Min(255, c.R + (255 - c.R) * p / 100),
                Math.Min(255, c.G + (255 - c.G) * p / 100),
                Math.Min(255, c.B + (255 - c.B) * p / 100));
        }
        public static Color Darken(Color c, int pct)
        {
            int p = pct;
            return Color.FromArgb(
                Math.Max(0, c.R - c.R * p / 100),
                Math.Max(0, c.G - c.G * p / 100),
                Math.Max(0, c.B - c.B * p / 100));
        }
    }

    // ─── Barra de progreso con gradiente (owner-drawn) ────────
    class ModernProgress : Control
    {
        private int value = 0;
        public int Value { get { return value; } set { this.value = Math.Max(0, Math.Min(Maximum, value)); Invalidate(); } }
        public int Maximum { get; set; }
        public Color TrackColor { get; set; }
        public Color FillA { get; set; }
        public Color FillB { get; set; }
        public int Radius { get; set; }

        public ModernProgress()
        {
            Maximum = 100;
            TrackColor = Palette.Slate200;
            FillA = Palette.Indigo;
            FillB = Palette.Violet;
            Radius = 6;
            SetStyle(ControlStyles.AllPaintingInWmPaint | ControlStyles.UserPaint |
                     ControlStyles.OptimizedDoubleBuffer | ControlStyles.ResizeRedraw, true);
            this.Size = new Size(300, 14);
        }

        protected override void OnPaint(PaintEventArgs e)
        {
            e.Graphics.SmoothingMode = SmoothingMode.AntiAlias;
            var r = ClientRectangle;
            r.Width--; r.Height--;

            using (var track = RoundedButton.RoundedRect(r, Radius))
            using (var tb = new SolidBrush(TrackColor))
                e.Graphics.FillPath(tb, track);

            int w = (int)(r.Width * (double)value / Maximum);
            if (w > 0)
            {
                var fillR = new Rectangle(r.X, r.Y, w, r.Height);
                using (var path = RoundedButton.RoundedRect(fillR, Radius))
                using (var gbrush = new LinearGradientBrush(fillR, FillA, FillB, 0f))
                    e.Graphics.FillPath(gbrush, path);
            }
        }
    }

    // ─── Panel con gradiente en el borde superior ────────────
    class BrandHeader : Control
    {
        public BrandHeader()
        {
            SetStyle(ControlStyles.AllPaintingInWmPaint | ControlStyles.UserPaint |
                     ControlStyles.OptimizedDoubleBuffer | ControlStyles.ResizeRedraw, true);
            this.Height = 92;
            this.Dock = DockStyle.Top;
        }

        protected override void OnPaint(PaintEventArgs e)
        {
            var r = ClientRectangle;
            using (var b = new LinearGradientBrush(r, Palette.Navy, Palette.Navy2, 40f))
                e.Graphics.FillRectangle(b, r);

            // Texto de marca
            using (var f = new Font("Segoe UI", 24, FontStyle.Bold))
            using (var b = new SolidBrush(Palette.White))
                e.Graphics.DrawString("SIGEJUB", f, b, 26, 18);

            using (var f = new Font("Segoe UI", 9.5f, FontStyle.Regular))
            using (var b = new SolidBrush(Color.FromArgb(180, 255, 255, 255)))
                e.Graphics.DrawString("Sistema Integral de Gestión de Jubilaciones", f, b, 27, 56);

            // Sello "Instalador"
            using (var f = new Font("Segoe UI", 9, FontStyle.Bold))
            using (var brushA = new LinearGradientBrush(new Rectangle(0, 0, 20, 20), Palette.Indigo, Palette.Violet, 45f))
            {
                SizeF s = e.Graphics.MeasureString("INSTALADOR", f);
                var box = new RectangleF(r.Right - s.Width - 46, r.Height / 2 - 16, s.Width + 26, 34);
                using (var path = RoundedButton.RoundedRect(Rectangle.Round(box), 6))
                    e.Graphics.FillPath(brushA, path);
                e.Graphics.DrawString("INSTALADOR", f, new SolidBrush(Color.White), box.X + 9, box.Y + 8);
            }
        }
    }

    // ─── Formulario principal ───────────────────────────────
    public class InstallerForm : Form
    {
        private RichTextBox logBox;
        private ModernProgress progressBar;
        private RoundedButton btnInstall;
        private RoundedButton btnConfig;
        private RoundedButton btnClose;
        private CheckBox chkShortcut;
        private Label lblStatus;
        private bool installing = false;

        private string dbHost = "127.0.0.1";
        private string dbPort = "3306";
        private string dbName = "bd-sigejub";
        private string dbUser = "root";
        private string dbPass = "";
        private string dbEngine = "mysql";
        private int selectedPort = 8000;

        public InstallerForm()
        {
            this.Text = "SIGEJUB - Instalador";
            this.ClientSize = new Size(680, 500);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.FormBorderStyle = FormBorderStyle.FixedSingle;
            this.MaximizeBox = false;
            this.Icon = LoadIcon();
            this.BackColor = Palette.Surface;
            this.Font = new Font("Segoe UI", 9f);

            // Cabecera de marca
            var header = new BrandHeader { Height = 92, Dock = DockStyle.Top };
            this.Controls.Add(header);

            // Panel de contenido
            var body = new Panel { Dock = DockStyle.Fill, BackColor = Palette.Surface, Padding = new Padding(24) };
            this.Controls.Add(body);

            // Log (tarjeta oscura con terminal)
            logBox = new RichTextBox
            {
                Location = new Point(0, 0),
                Size = new Size(body.Width - 48, 210),
                ReadOnly = true,
                BackColor = Palette.Navy,
                ForeColor = Color.FromArgb(226, 232, 240),
                Font = new Font("Consolas", 9.5f),
                BorderStyle = BorderStyle.None,
                DetectUrls = false
            };
            SetRoundedRect(logBox, 10);

            // Barra de progreso
            progressBar = new ModernProgress
            {
                Location = new Point(0, 224),
                Size = new Size(body.Width - 48, 16),
                Maximum = 100
            };

            // Estado
            lblStatus = new Label
            {
                Location = new Point(0, 250),
                Size = new Size(body.Width - 48, 22),
                Text = "Listo para instalar",
                ForeColor = Palette.Slate600,
                Font = new Font("Segoe UI", 9f)
            };

            // Acceso directo
            chkShortcut = new CheckBox
            {
                Text = "Crear acceso directo en el escritorio",
                Location = new Point(0, 292),
                Size = new Size(260, 24),
                ForeColor = Palette.Slate600,
                Checked = true,
                Font = new Font("Segoe UI", 9f)
            };
            chkShortcut.FlatStyle = FlatStyle.Flat;

            // Botón secundario: configurar BD
            btnConfig = new RoundedButton
            {
                Text = "Configurar BD...",
                Location = new Point(0, body.Height - 70),
                Size = new Size(150, 42),
                NormalBack = Palette.White,
                HoverBack = Palette.Slate100,
                Border = Palette.Slate200,
                ForeColor = Palette.Navy,
                IsPrimary = false,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Regular)
            };
            btnConfig.Click += (s, e) => ShowConfig();

            // Botón cerrar
            btnClose = new RoundedButton
            {
                Text = "Cerrar",
                Location = new Point(body.Width - 48 - 200 - 200, body.Height - 70),
                Size = new Size(110, 42),
                NormalBack = Palette.White,
                HoverBack = Palette.Slate100,
                Border = Palette.Slate200,
                ForeColor = Palette.Slate600,
                IsPrimary = false,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Regular)
            };
            btnClose.Click += (s, e) => this.Close();

            // Botón principal: instalar / iniciar
            btnInstall = new RoundedButton
            {
                Text = "Instalar",
                Location = new Point(body.Width - 48 - 150, body.Height - 70),
                Size = new Size(150, 42),
                NormalBack = Palette.Indigo,
                HoverBack = Palette.IndigoDark,
                Font = new Font("Segoe UI", 10f, FontStyle.Bold)
            };
            btnInstall.Click += BtnInstall_Click;

            body.Controls.AddRange(new Control[] { logBox, progressBar, lblStatus, chkShortcut, btnConfig, btnClose, btnInstall });

            logBox.AppendText(" Bienvenido al instalador profesional de SIGEJUB\n");
            logBox.AppendText(" Requisitos: PHP 8.2+, Composer, MySQL o PostgreSQL\n");
            logBox.AppendText(" Puedes configurar la BD y el gestor antes de instalar.\n\n");

            // Escalar posiciones cuando se redimensione el formulario
            this.Resize += (s, e) => { AlignBody(body); };
            AlignBody(body);
        }

        private void AlignBody(Panel body)
        {
            int w = body.Width - 48;
            if (w > 0)
            {
                logBox.Width = w;
                progressBar.Width = w;
                lblStatus.Width = w;
                btnConfig.Location = new Point(0, body.Height - 70);
                btnClose.Location = new Point(body.Width - 48 - 200 - 110, body.Height - 70);
                btnInstall.Location = new Point(body.Width - 48 - 150, body.Height - 70);
            }
        }

        private void SetRoundedRect(Control c, int radius)
        {
            c.Region = new Region(RoundedButton.RoundedRect(new Rectangle(0, 0, c.Width, c.Height), radius));
        }

        private Icon LoadIcon()
        {
            var path = Path.Combine(Application.StartupPath, "public", "img",
                "imagen_2026-05-19_065531142.ico");
            if (File.Exists(path))
                try { return new Icon(path); } catch { }
            return SystemIcons.Application;
        }

        // ─── Diálogo moderno de configuración de BD ───────────
        private void ShowConfig()
        {
            var frm = new Form
            {
                Text = "Base de Datos",
                ClientSize = new Size(430, 380),
                StartPosition = FormStartPosition.CenterParent,
                FormBorderStyle = FormBorderStyle.FixedDialog,
                MaximizeBox = false, MinimizeBox = false,
                ShowInTaskbar = false,
                BackColor = Palette.Surface,
                Font = new Font("Segoe UI", 9f)
            };

            // Cabecera del diálogo
            var dlgHeader = new Panel { Dock = DockStyle.Top, Height = 64, BackColor = Palette.Navy2 };
            var dlgTitle = new Label
            {
                Text = "Configuración de Base de Datos",
                ForeColor = Color.White, Font = new Font("Segoe UI", 13, FontStyle.Bold),
                AutoSize = true, Location = new Point(20, 14)
            };
            var dlgSub = new Label
            {
                Text = "Selecciona el gestor y los datos de conexión",
                ForeColor = Color.FromArgb(180, 255, 255, 255), Font = new Font("Segoe UI", 8.5f),
                AutoSize = true, Location = new Point(21, 38)
            };
            dlgHeader.Controls.Add(dlgTitle);
            dlgHeader.Controls.Add(dlgSub);
            frm.Controls.Add(dlgHeader);

            int y = 86;
            var lblEngine = new Label { Text = "Gestor de base de datos:", Location = new Point(20, y), Size = new Size(300, 18), ForeColor = Palette.Slate600, Font = new Font("Segoe UI", 9f, FontStyle.Bold) };
            frm.Controls.Add(lblEngine);
            y += 24;

            var rdoMysql = new RadioButton
            {
                Text = "MySQL / MariaDB (phpMyAdmin)",
                Location = new Point(24, y), Size = new Size(210, 22), Checked = dbEngine == "mysql",
                Font = new Font("Segoe UI", 9f)
            };
            var rdoPgsql = new RadioButton
            {
                Text = "PostgreSQL",
                Location = new Point(236, y), Size = new Size(120, 22), Checked = dbEngine == "pgsql",
                Font = new Font("Segoe UI", 9f)
            };
            frm.Controls.Add(rdoMysql);
            frm.Controls.Add(rdoPgsql);
            y += 30;

            var txtHost = AddField(frm, "Host:", ref y, dbHost);
            var txtPort = AddField(frm, "Puerto:", ref y, dbPort);
            var txtDb   = AddField(frm, "BD:", ref y, dbName);
            var txtUser = AddField(frm, "Usuario:", ref y, dbUser);
            var txtPass = AddField(frm, "Clave:", ref y, dbPass, true);
            y += 6;

            EventHandler engineChanged = (s, e) =>
            {
                if (rdoMysql.Checked) { txtPort.Text = "3306"; txtUser.Text = "root"; }
                else { txtPort.Text = "5432"; txtUser.Text = "postgres"; }
            };
            rdoMysql.CheckedChanged += engineChanged;
            rdoPgsql.CheckedChanged += engineChanged;

            var btnOk = new RoundedButton
            {
                Text = "Guardar", Location = new Point(20, y), Size = new Size(100, 40),
                NormalBack = Palette.Indigo, HoverBack = Palette.IndigoDark, DialogResult = DialogResult.OK,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold)
            };
            var btnCancel = new RoundedButton
            {
                Text = "Cancelar", Location = new Point(128, y), Size = new Size(100, 40),
                NormalBack = Palette.White, HoverBack = Palette.Slate100, Border = Palette.Slate200,
                ForeColor = Palette.Slate600, DialogResult = DialogResult.Cancel, IsPrimary = false,
                Font = new Font("Segoe UI", 9.5f)
            };
            frm.Controls.Add(btnOk);
            frm.Controls.Add(btnCancel);
            frm.AcceptButton = btnOk;

            if (frm.ShowDialog() == DialogResult.OK)
            {
                dbEngine = rdoPgsql.Checked ? "pgsql" : "mysql";
                dbHost = string.IsNullOrWhiteSpace(txtHost.Text) ? "127.0.0.1" : txtHost.Text;
                dbPort = string.IsNullOrWhiteSpace(txtPort.Text) ? (dbEngine == "pgsql" ? "5432" : "3306") : txtPort.Text;
                dbName = string.IsNullOrWhiteSpace(txtDb.Text) ? "bd-sigejub" : txtDb.Text;
                dbUser = string.IsNullOrWhiteSpace(txtUser.Text) ? (dbEngine == "pgsql" ? "postgres" : "root") : txtUser.Text;
                dbPass = txtPass.Text;
                AppendLog("[OK] Configuración guardada. Gestor: " + (dbEngine == "pgsql" ? "PostgreSQL" : "MySQL/MariaDB"), Color.Cyan);
            }
        }

        private TextBox AddField(Form form, string label, ref int y, string defaultValue, bool password = false)
        {
            var lbl = new Label { Text = label, Location = new Point(20, y + 4), Size = new Size(80, 24), ForeColor = Palette.Slate600, Font = new Font("Segoe UI", 9f, FontStyle.Bold) };
            var tb = new TextBox
            {
                Text = defaultValue,
                Location = new Point(100, y),
                Size = new Size(300, 26),
                Font = new Font("Segoe UI", 9.5f),
                BorderStyle = BorderStyle.FixedSingle,
                BackColor = Color.White
            };
            if (password) tb.PasswordChar = '*';
            form.Controls.Add(lbl);
            form.Controls.Add(tb);
            y += 32;
            return tb;
        }

        // ─── Log con colores ─────────────────────────────────
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
            progressBar.Value = value;
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

        // ─── Lógica de instalación (preservada) ──────────────
        private void BtnInstall_Click(object sender, EventArgs e)
        {
            if (installing) return;
            installing = true;
            btnInstall.Enabled = false;
            btnInstall.Text = "Instalando...";
            btnInstall.NormalBack = Palette.Slate500;
            btnInstall.HoverBack = Palette.Slate600;
            SetStatus("Instalando...");

            string output;

            // 1: PHP
            AppendLog("[1/7] Verificando PHP...", Color.Yellow);
            if (!RunCmd("where", "php", out output))
            {
                AppendLog("[ERROR] PHP no encontrado en PATH", Color.Red);
                goto error;
            }
            string phpVer = "";
            RunCmd("php", "-r \"echo PHP_MAJOR_VERSION;\"", out phpVer);
            AppendLog("[OK] PHP " + phpVer + ".x", Color.Lime);
            SetProgress(14);

            // 2: Composer
            AppendLog("[2/7] Verificando Composer...", Color.Yellow);
            if (!RunCmd("where", "composer", out output))
            {
                AppendLog("[ERROR] Composer no encontrado", Color.Red);
                goto error;
            }
            AppendLog("[OK] Composer encontrado", Color.Lime);
            SetProgress(28);

            // 3: .env
            AppendLog("[3/7] Configurando .env...", Color.Yellow);
            string envPath = Path.Combine(Application.StartupPath, ".env");
            string envExample = Path.Combine(Application.StartupPath, ".env.example");

            if (!File.Exists(envPath))
            {
                if (File.Exists(envExample))
                {
                    File.Copy(envExample, envPath);
                    AppendLog("[OK] .env creado", Color.Lime);
                }
                else { AppendLog("[ERROR] Falta .env.example", Color.Red); goto error; }
            }
            else AppendLog("[OK] .env existe", Color.Lime);

            string env = File.ReadAllText(envPath, Encoding.UTF8);
            env = RemoveEnvLine(env, "DB_HOST_PGSQL");
            env = RemoveEnvLine(env, "DB_PORT_PGSQL");
            env = RemoveEnvLine(env, "DB_DATABASE_PGSQL");
            env = RemoveEnvLine(env, "DB_USERNAME_PGSQL");
            env = RemoveEnvLine(env, "DB_PASSWORD_PGSQL");
            env = ReplaceEnv(env, "DB_CONNECTION", dbEngine);
            env = ReplaceEnv(env, "DB_HOST", dbHost);
            env = ReplaceEnv(env, "DB_PORT", dbPort);
            env = ReplaceEnv(env, "DB_DATABASE", dbName);
            env = ReplaceEnv(env, "DB_USERNAME", dbUser);
            env = ReplaceEnv(env, "DB_PASSWORD", dbPass);
            File.WriteAllText(envPath, env, Encoding.UTF8);
            AppendLog("[OK] BD configurada en .env (" + (dbEngine == "pgsql" ? "PostgreSQL" : "MySQL/MariaDB") + ")", Color.Lime);
            SetProgress(42);

            // 4: composer install
            AppendLog("[4/7] Instalando dependencias...", Color.Yellow);
            if (!RunCmd("composer", "install --no-interaction --no-ansi", out output))
                AppendLog("[ADVERTENCIA] composer con errores", Color.Orange);
            else AppendLog("[OK] Dependencias instaladas", Color.Lime);
            SetProgress(56);

            // 5: key:generate
            AppendLog("[5/7] Generando APP_KEY...", Color.Yellow);
            if (!RunCmd("php", "artisan key:generate --force --no-ansi", out output))
                AppendLog("[ADVERTENCIA] " + output, Color.Orange);
            else AppendLog("[OK] APP_KEY generada", Color.Lime);
            SetProgress(70);

            // 6: migrate
            AppendLog("[6/7] Ejecutando migraciones...", Color.Yellow);
            AppendLog("      (La BD debe existir en " + (dbEngine == "pgsql" ? "PostgreSQL" : "MySQL") + ")", Color.Gray);
            if (!RunCmd("php", "artisan migrate --force --no-ansi", out output))
                AppendLog("[ADVERTENCIA] Migraciones: " + output.Substring(0, Math.Min(300, output.Length)), Color.Orange);
            else AppendLog("[OK] Migraciones ejecutadas", Color.Lime);
            SetProgress(84);

            // 7: Finalizar
            AppendLog("[7/7] Finalizando...", Color.Yellow);
            string _out = "";
            RunCmd("php", "artisan optimize:clear --no-ansi", out _out);
            string pubStorage = Path.Combine(Application.StartupPath, "public", "storage");
            if (Directory.Exists(pubStorage)) Directory.Delete(pubStorage, true);
            RunCmd("php", "artisan storage:link --no-ansi", out _out);
            AppendLog("[OK] Cache + storage link", Color.Lime);
            SetProgress(92);

            selectedPort = FindFreePort();
            env = File.ReadAllText(envPath, Encoding.UTF8);
            env = ReplaceEnv(env, "APP_URL", "http://localhost:" + selectedPort);
            File.WriteAllText(envPath, env, Encoding.UTF8);
            AppendLog("[OK] Puerto " + selectedPort + " asignado", Color.Lime);
            SetProgress(100);

            // Acceso directo
            if (chkShortcut.Checked)
            {
                try
                {
                    string desktop = Environment.GetFolderPath(Environment.SpecialFolder.Desktop);
                    string appPath = Application.StartupPath;
                    string vbsPath = Path.Combine(appPath, "sigejub-start.vbs");
                    string lnkPath = Path.Combine(desktop, "SIGEJUB.lnk");
                    AppendLog("Creando acceso directo en: " + desktop, Color.Gray);

                    string vbs = Path.GetTempFileName() + ".vbs";
                    string ico = Path.Combine(appPath, "public", "img",
                        "imagen_2026-05-19_065531142.ico") + ", 0";
                    File.WriteAllText(vbs,
                        "Set ws = CreateObject(\"WScript.Shell\")\n" +
                        "Set sc = ws.CreateShortcut(\"" + lnkPath + "\")\n" +
                        "sc.TargetPath = \"" + vbsPath + "\"\n" +
                        "sc.WorkingDirectory = \"" + appPath + "\"\n" +
                        "sc.WindowStyle = 7\n" +
                        "sc.Description = \"SIGEJUB - Sistema de Gestion de Jubilaciones\"\n" +
                        "sc.IconLocation = \"" + ico + "\"\n" +
                        "sc.Save()\n");

                    Process.Start("cscript", "//Nologo \"" + vbs + "\"").WaitForExit();
                    try { File.Delete(vbs); } catch { }

                    if (File.Exists(lnkPath))
                        AppendLog("[OK] Acceso directo: " + lnkPath, Color.Lime);
                    else AppendLog("[AVISO] El acceso directo podria no haberse creado", Color.Orange);
                }
                catch (Exception ex)
                {
                    AppendLog("[AVISO] Acceso directo: " + ex.Message, Color.Orange);
                }
            }

            AppendLog("", Color.White);
            AppendLog("  INSTALACIÓN COMPLETADA", Color.Cyan);
            AppendLog("  Puerto: " + selectedPort, Color.Cyan);
            AppendLog("  URL:    http://localhost:" + selectedPort, Color.Cyan);

            btnInstall.Text = "Iniciar";
            btnInstall.NormalBack = Palette.Green;
            btnInstall.HoverBack = LightenColor(Palette.Green, 6);
            btnInstall.Click -= BtnInstall_Click;
            btnInstall.Click += (s2, e2) =>
            {
                string vbsLaunch = Path.Combine(Application.StartupPath, "sigejub-start.vbs");
                if (File.Exists(vbsLaunch))
                    Process.Start(vbsLaunch);
                else
                {
                    Process.Start("http://localhost:" + selectedPort);
                    Process.Start(new ProcessStartInfo
                    {
                        FileName = "php",
                        Arguments = "artisan serve --port=" + selectedPort,
                        WorkingDirectory = Application.StartupPath,
                        UseShellExecute = true
                    });
                }
                this.Close();
            };
            btnInstall.Enabled = true;
            installing = false;
            SetStatus("Instalación completa en puerto " + selectedPort);
            return;

        error:
            AppendLog("[ERROR] Instalación cancelada.", Color.Red);
            SetStatus("Error durante la instalación");
            btnInstall.Text = "Instalar";
            btnInstall.NormalBack = Palette.Indigo;
            btnInstall.HoverBack = Palette.IndigoDark;
            btnInstall.Enabled = true;
            installing = false;
        }

        private static Color LightenColor(Color c, int pct)
        {
            int p = pct;
            return Color.FromArgb(
                Math.Min(255, c.R + (255 - c.R) * p / 100),
                Math.Min(255, c.G + (255 - c.G) * p / 100),
                Math.Min(255, c.B + (255 - c.B) * p / 100));
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

        private string RemoveEnvLine(string content, string key)
        {
            var lines = content.Split('\n');
            var result = new System.Collections.Generic.List<string>();
            foreach (var line in lines)
                if (!line.StartsWith(key + "=", StringComparison.OrdinalIgnoreCase))
                    result.Add(line);
            return string.Join("\n", result);
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
