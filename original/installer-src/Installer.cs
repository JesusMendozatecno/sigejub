using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Drawing;
using System.Drawing.Drawing2D;
using System.IO;
using System.IO.Compression;
using System.Net;
using System.Runtime.InteropServices;
using System.Text;
using System.Threading;
using System.Windows.Forms;
using Microsoft.Win32;

namespace SIGEJUB_Installer
{
    static class Program
    {
        [System.Runtime.InteropServices.DllImport("user32.dll")]
        private static extern bool SetProcessDPIAware();

        // Guarda el detalle de cualquier excepción no controlada para diagnóstico.
        private static void SaveCrash(Exception ex)
        {
            try
            {
                string log = Path.Combine(Path.GetTempPath(), "sigejub-installer-error.log");
                File.WriteAllText(log, DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss") + "\r\n" + ex.ToString());
            }
            catch { }
        }

        [STAThread]
        static void Main()
        {
            AppDomain.CurrentDomain.UnhandledException += (s, e) =>
            {
                SaveCrash(e.ExceptionObject as Exception ?? new Exception(e.ExceptionObject == null ? "null" : e.ExceptionObject.ToString()));
            };
            Application.ThreadException += (s, e) =>
            {
                SaveCrash(e.Exception);
                try { MessageBox.Show("Ocurrió un error:\n\n" + e.Exception.Message + "\n\nDetalle guardado en " + Path.Combine(Path.GetTempPath(), "sigejub-installer-error.log"), "SIGEJUB", MessageBoxButtons.OK, MessageBoxIcon.Error); } catch { }
            };
            // Evita que el escalado de DPI desincronice texto y coordenadas
            // (causa que el texto se monte sobre el panel lateral en monitores escalados)
            try { SetProcessDPIAware(); } catch { }
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            try
            {
                Application.Run(new InstallerForm());
            }
            catch (Exception ex)
            {
                SaveCrash(ex);
                try { MessageBox.Show("El instalador no pudo iniciar:\n\n" + ex.Message + "\n\nDetalle guardado en " + Path.Combine(Path.GetTempPath(), "sigejub-installer-error.log"), "SIGEJUB", MessageBoxButtons.OK, MessageBoxIcon.Error); } catch { }
            }
        }
    }

    // Paleta de colores reutilizable
    public static class P
    {
        public static readonly Color Navy   = Color.FromArgb(15, 23, 42);
        public static readonly Color Indigo = Color.FromArgb(99, 102, 241);
        public static readonly Color IndigoDark = Color.FromArgb(79, 70, 229);
        public static readonly Color Bg     = Color.FromArgb(248, 250, 252);
        public static readonly Color Card   = Color.White;
        public static readonly Color Sidebar= Color.FromArgb(15, 23, 42);
        public static readonly Color Muted  = Color.FromArgb(100, 116, 139);
        public static readonly Color Text   = Color.FromArgb(30, 41, 59);
        public static readonly Color Border = Color.FromArgb(226, 232, 240);
        public static readonly Color Green  = Color.FromArgb(22, 163, 74);
    }

    // Botón moderno (redondeado, con hover, sin borde nativo)
    public class RoundBtn : Button
    {
        public bool IsPrimary { get; set; }
        public RoundBtn()
        {
            FlatStyle = FlatStyle.Flat;
            // Limpiar TODOS los bordes por defecto del sistema (evita el recuadro
            // oscuro que asoma detrás del color morado en el borde del botón).
            FlatAppearance.BorderSize = 0;
            // ButtonBase no admite BorderColor en Transparent (lanza NotSupportedException);
            // el recuadro nativo se elimina con BorderSize=0 y nuestro OnPaint dibuja el borde.
            FlatAppearance.BorderColor = BackColor;
            FlatAppearance.MouseOverBackColor = Color.Transparent;
            FlatAppearance.MouseDownBackColor = Color.Transparent;
            BackColor = P.Indigo;
            ForeColor = Color.White;
            Font = new Font("Segoe UI", 10, FontStyle.Bold);
            Cursor = Cursors.Hand;
            Height = 40;
            UseVisualStyleBackColor = false;
            SetStyle(ControlStyles.UserPaint | ControlStyles.AllPaintingInWmPaint |
                     ControlStyles.OptimizedDoubleBuffer | ControlStyles.ResizeRedraw, true);
        }
        protected override void OnPaint(PaintEventArgs e)
        {
            var g = e.Graphics;
            g.SmoothingMode = SmoothingMode.AntiAlias;

            var bg = IsPrimary ? P.Indigo : BackColor;
            if (IsPrimary)
            {
                if (Focused) bg = P.IndigoDark;
                else if (ClientRectangle.Contains(PointToClient(Cursor.Position))) bg = Color.FromArgb(105, 108, 245);
            }
            else if (ClientRectangle.Contains(PointToClient(Cursor.Position)))
            {
                bg = Color.FromArgb(226, 232, 240);
            }

            // Rellenar TODO el rectángulo del botón para que el fondo nativo nunca
            // se vea en las esquinas ni en el borde (elimina el recuadro negro).
            using (var b = new SolidBrush(bg)) g.FillRectangle(b, 0, 0, Width, Height);

            var rc = new Rectangle(1, 1, Width - 3, Height - 3);
            using (var path = Rounded(rc, 10))
            {
                using (var b = new SolidBrush(bg)) g.FillPath(b, path);
            }

            // Borde sutil y definido (nada de bordes nativos de Windows).
            Color border = IsPrimary ? Color.FromArgb(79, 70, 229) : Color.FromArgb(203, 213, 225);
            using (var path = Rounded(rc, 10))
            using (var pen = new Pen(border, 1f))
                g.DrawPath(pen, path);

            string txt = Text;
            if (IsPrimary && !string.IsNullOrEmpty(Text) && !Text.EndsWith("→"))
            {
                txt = Text + "  →";
            }
            TextRenderer.DrawText(g, txt, Font, rc, ForeColor,
                TextFormatFlags.HorizontalCenter | TextFormatFlags.VerticalCenter | TextFormatFlags.EndEllipsis);
        }
        public static GraphicsPath Rounded(Rectangle r, int d)
        {
            var p = new GraphicsPath();
            int d2 = d * 2;
            p.AddArc(r.X, r.Y, d2, d2, 180, 90);
            p.AddArc(r.Right - d2, r.Y, d2, d2, 270, 90);
            p.AddArc(r.Right - d2, r.Bottom - d2, d2, d2, 0, 90);
            p.AddArc(r.X, r.Bottom - d2, d2, d2, 90, 90);
            p.CloseFigure();
            return p;
        }
    }

    // Panel de pasos (sidebar)
    public class StepsPanel : Panel
    {
        public List<string> Items = new List<string>();
        public int Current = 0;
        protected override void OnPaint(PaintEventArgs e)
        {
            var g = e.Graphics;
            g.SmoothingMode = SmoothingMode.AntiAlias;
            using (var b = new SolidBrush(P.Sidebar)) g.FillRectangle(b, ClientRectangle);
            // Logo
            using (var b = new SolidBrush(Color.White)) g.DrawString("SIGEJUB", new Font("Segoe UI", 20, FontStyle.Bold), b, 34, 28);
            using (var b = new SolidBrush(Color.FromArgb(165, 180, 252)))
                g.DrawString("Instalador de Sistema", new Font("Segoe UI", 9), b, 34, 62);

            if (Items.Count > 0)
            {
                int top = 110;
                int stepH = 56;
                for (int i = 0; i < Items.Count; i++)
                {
                    int cy = top + i * stepH;
                    bool active = (i == Current);
                    bool done = (i < Current);
                    // linea conectora
                    if (i > 0)
                    {
                        using (var pen = new Pen(done ? P.Indigo : Color.FromArgb(51, 65, 85), 2))
                            g.DrawLine(pen, 50, cy - stepH + 44, 50, cy + 6);
                    }
                    var circle = new Rectangle(36, cy, 28, 28);
                    using (var b = new SolidBrush(active ? P.Indigo : Color.FromArgb(30, 41, 59)))
                        g.FillEllipse(b, circle);
                    using (var pen = new Pen(done || active ? P.Indigo : Color.FromArgb(71, 85, 105), 1.5f))
                        g.DrawEllipse(pen, circle);
                    string num = done ? "\u2713" : (i + 1).ToString();
                    using (var b = new SolidBrush(active || done ? Color.White : Color.FromArgb(148, 163, 184)))
                        g.DrawString(num, new Font("Segoe UI", 10, FontStyle.Bold), b, circle.X + 8, circle.Y + 5);
                    using (var b = new SolidBrush(active ? Color.White : Color.FromArgb(148, 163, 184)))
                        g.DrawString(Items[i], new Font("Segoe UI", 10, FontStyle.Bold), b, 78, cy + 4);
                }
            }
        }
        public void RefreshSteps() { Invalidate(); }
    }

    // Instalador principal
    public class InstallerForm : Form
    {
        private StepsPanel steps;
        private Panel content;
        private Panel currentPage;
        private RoundBtn btnBack, btnNext, btnStart;
        private Label lblTitle;

        private int step = 0;
        private const int STEP_WELCOME = 0;
        private const int STEP_FOLDER  = 1;
        private const int STEP_DB      = 2;
        private const int STEP_INSTALL = 3;
        private const int STEP_DONE    = 4;
        private const int STEP_COUNT   = 4;

        // URLs de descarga de los componentes (modo online)
        private const string PHP_PAGE    = "https://windows.php.net/downloads/releases/";
        private const string COMPOSER_URL = "https://getcomposer.org/download/latest-stable/composer.phar";
        // UTF-8 SIN BOM: los scripts .vbs/.bat/.env fallan si el archivo empieza con BOM
        private static readonly Encoding Utf8NoBom = new UTF8Encoding(false);
        // Versiones con fallback: si una URL falla o descarga un zip corrupto, se prueba la siguiente
        private static readonly string[] MARIADB_URLS =
        {
            "https://archive.mariadb.org/mariadb-11.4.5/winx64-packages/mariadb-11.4.5-winx64.zip",
            "https://archive.mariadb.org/mariadb-10.11.11/winx64-packages/mariadb-10.11.11-winx64.zip"
        };
        private static readonly string[] PGSQL_URLS =
        {
            "https://get.enterprisedb.com/postgresql/postgresql-16.8-1-windows-x64-binaries.zip",
            "https://get.enterprisedb.com/postgresql/postgresql-15.8-1-windows-x64-binaries.zip"
        };

        // Estado de componentes resueltos durante la instalación
        private bool online = false;
        private string phpExe = null;       // php.exe del sistema o el portable local
        private bool phpPortable = false;   // true si se instaló PHP local dentro de la app
        private string composerPhar = null; // ruta de composer.phar local (si no hay composer en el sistema)
        private string composerCmd = null;  // ruta resuelta del composer del sistema (p.ej. composer.bat)
        private bool dbPortable = false;    // true si se instaló un servidor BD local
        private string dbStartCmd = null;   // comando para arrancar la BD portable (vbs/inicio)

        // Config
        private string installPath = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ProgramFilesX86), "SIGEJUB");
        private bool createShortcut = true;
        private string dbHost = "127.0.0.1";
        private string dbPort = "3306";
        private string dbName = "bd-sigejub";
        private string dbUser = "root";
        private string dbPass = "";
        private string dbEngine = "mysql";
        private int selectedPort = 8000;

        // Controles de página
        private TextBox txtPath;
        private CheckBox chkShortcut;
        private RadioButton rdoMysql, rdoPgsql, rdoSqlite;
        private TextBox txtHost, txtPort, txtDb, txtUser, txtPass;
        private RichTextBox logBox;
        private ProgressBar progressBar;
        private Label lblStatus, lblDoneUrl;
        private bool installing = false;

        // Debounce para el redimensionado
        private System.Windows.Forms.Timer resizeTimer;

        private string SourceRoot = null;

        public InstallerForm()
        {
            // Detectar la raíz de la app (carpeta del exe)
            SourceRoot = Application.StartupPath;
            if (File.Exists(Path.Combine(SourceRoot, "artisan"))) { }
            else if (File.Exists(Path.Combine(SourceRoot, "app", "artisan"))) SourceRoot = Path.Combine(SourceRoot, "app");
            else if (File.Exists(Path.Combine(SourceRoot, "..", "artisan"))) SourceRoot = Path.GetFullPath(Path.Combine(SourceRoot, ".."));

            this.Text = "SIGEJUB - Instalador";
            this.Size = new Size(980, 620);
            this.MinimumSize = new Size(880, 580);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.FormBorderStyle = FormBorderStyle.Sizable;
            this.MaximizeBox = false;
            this.Icon = LoadIcon();
            this.BackColor = P.Bg;
            this.DoubleBuffered = true;

            steps = new StepsPanel { Dock = DockStyle.Left, Width = 270 };
            steps.Items = new List<string> { "Bienvenida", "Carpeta de instalación", "Base de datos", "Instalación" };
            steps.Current = 0;

            content = new Panel { Dock = DockStyle.Fill, BackColor = P.Bg };

            // IMPORTANTE: en WinForms el docking se resuelve en z-order inverso
            // (el último agregado se acopla primero). content (Dock=Fill) debe ir
            // PRIMERO y steps (Dock=Left) DESPUÉS; de lo contrario el sidebar se
            // superpone a la izquierda del contenido, tapando textos y encabezados.
            this.Controls.Add(content);
            this.Controls.Add(steps);

            // Botones inferiores
            btnBack = new RoundBtn { Text = "Atrás", IsPrimary = false, Width = 110 };
            btnBack.BackColor = P.Bg; btnBack.ForeColor = P.Muted; btnBack.Font = new Font("Segoe UI", 10);
            btnNext = new RoundBtn { Text = "Siguiente", IsPrimary = true, Width = 140 };
            btnBack.Click += (s, e) => Go(step - 1);
            btnNext.Click += (s, e) => Next();
            content.Controls.Add(btnBack);
            content.Controls.Add(btnNext);

            btnStart = new RoundBtn { Text = "Iniciar SIGEJUB", IsPrimary = true, Width = 200, Height = 44 };
            btnStart.Click += (s, e) => DoStart();
            content.Controls.Add(btnStart);

            // Renderizamos tras el primer layout (cuando content ya tiene su tamaño real)
            this.Load += (s, e) => ShowPage(STEP_WELCOME);

            // Debounce del redimensionado: re-maqueta página estática
            resizeTimer = new System.Windows.Forms.Timer { Interval = 120 };
            resizeTimer.Tick += (s, e) => { resizeTimer.Stop(); RebuildStaticPage(); };
            this.Resize += (s, e) =>
            {
                RepositionButtons();
                if (IsStaticStep) { resizeTimer.Stop(); resizeTimer.Start(); }
            };
        }

        // Indica si la página actual es de contenido estático (reconstruible)
        private bool IsStaticStep
        {
            get { return step == STEP_WELCOME || step == STEP_FOLDER || step == STEP_DB || step == STEP_DONE; }
        }

        private void RepositionButtons()
        {
            if (content == null) return;
            if (content.ClientSize.Width <= 0 || content.ClientSize.Height <= 0) return;
            int w = content.ClientSize.Width;
            int h = content.ClientSize.Height;
            btnNext.Location = new Point(w - 160, h - 58);
            btnBack.Location = new Point(w - 290, h - 58);
            btnStart.Location = new Point(w - 220, h - 64);
            btnNext.BringToFront();
            btnBack.BringToFront();
            btnStart.BringToFront();
        }

        private void RebuildStaticPage()
        {
            if (content == null) return;
            if (content.ClientSize.Width <= 0 || content.ClientSize.Height <= 0) return;
            if (!IsStaticStep) return;
            // Guardar estado de los inputs a variables de clase
            if (step == STEP_FOLDER && txtPath != null && !txtPath.IsDisposed) installPath = txtPath.Text.Trim();
            if (step == STEP_DB && txtHost != null && !txtHost.IsDisposed) ReadDb();
            this.SuspendLayout();
            ShowPage(step);
            this.ResumeLayout(true);
        }

        private void Next()
        {
            if (step == STEP_FOLDER && !ValidateFolder()) return;
            if (step == STEP_DB) { ReadDb(); }
            if (step == STEP_INSTALL)
            {
                if (installing) return;
                SetProgress(0);
                SetStatus("Reintentando...");
                StartInstall();
                return;
            }
            Go(step + 1);
        }

        private void Go(int target)
        {
            if (target < STEP_WELCOME || target > STEP_COUNT) return;
            step = target;
            steps.Current = target == STEP_DONE ? STEP_COUNT - 1 : target;
            steps.RefreshSteps();
            ShowPage(step);
        }

        private Panel MakeHeader(string title, string subtitle)
        {
            var p = new Panel { Location = new Point(0, 0), Size = new Size(ContentW, 84), BackColor = P.Bg };
            lblTitle = new Label
            {
                Text = title, AutoSize = true,
                Font = new Font("Segoe UI", 17, FontStyle.Bold),
                ForeColor = P.Navy, Location = new Point(18, 16), MaximumSize = new Size(ContentW - 36, 40)
            };
            var sub = new Label
            {
                Text = subtitle, AutoSize = true,
                Font = new Font("Segoe UI", 10),
                ForeColor = P.Muted, Location = new Point(18, 52), MaximumSize = new Size(ContentW - 36, 24)
            };
            p.Controls.Add(lblTitle);
            p.Controls.Add(sub);
            return p;
        }

        private void ClearContent()
        {
            foreach (Control c in content.Controls)
            {
                if (c == btnBack || c == btnNext || c == btnStart) continue;
                c.Dispose();
            }
            content.Controls.Clear();
            content.Controls.Add(btnStart);
            content.Controls.Add(btnBack);
            content.Controls.Add(btnNext);
        }

        private void ShowPage(int s)
        {
            ClearContent();
            currentPage = new Panel { Dock = DockStyle.Fill, BackColor = P.Bg };
            content.Controls.Add(currentPage);
            content.Controls.SetChildIndex(currentPage, 0);

            switch (s)
            {
                case STEP_WELCOME: BuildWelcome(); break;
                case STEP_FOLDER: BuildFolder(); break;
                case STEP_DB: BuildDb(); break;
                case STEP_INSTALL: BuildInstall(); break;
                case STEP_DONE: BuildDone(); break;
            }

            btnBack.Visible = (s != STEP_WELCOME && s != STEP_DONE && s != STEP_INSTALL);
            btnNext.Visible = (s != STEP_DONE && s != STEP_INSTALL);
            btnStart.Visible = (s == STEP_DONE);
            if (s == STEP_WELCOME) { btnNext.Text = "Siguiente"; }
            else if (s == STEP_FOLDER) { btnNext.Text = "Siguiente"; }
            else if (s == STEP_DB) { btnNext.Text = "Instalar"; }
            else if (s == STEP_INSTALL) { btnNext.Text = "Instalando..."; btnNext.Enabled = false; }
            btnBack.Enabled = !(s == STEP_INSTALL || s == STEP_DONE);

            RepositionButtons();

            if (s == STEP_INSTALL) StartInstall();
        }

        // Ancho/alto útil para las páginas (respetando margen y botones inferiores)
        private int ContentW { get { return Math.Max(1, content.ClientSize.Width - 8); } }
        private int ContentH { get { return Math.Max(1, content.ClientSize.Height - 8); } }

        // ─── PÁGINA 0: BIENVENIDA ───
        private void BuildWelcome()
        {
            var h = MakeHeader("¡Bienvenido a SIGEJUB!", "Sistema Integral de Gestión de Jubilaciones");
            currentPage.Controls.Add(h);

            var card = new Panel
            {
                BackColor = P.Card, Location = new Point(18, 96),
                Size = new Size(ContentW - 36, ContentH - 176),
                Anchor = AnchorStyles.Left | AnchorStyles.Top | AnchorStyles.Right | AnchorStyles.Bottom
            };
            card.Paint += (s, e) =>
            {
                var g = e.Graphics; g.SmoothingMode = SmoothingMode.AntiAlias;
                using (var p = new Pen(P.Border, 1)) { g.DrawRectangle(p, 0, 0, card.Width - 1, card.Height - 1); }
            };

            var inner = new Panel { BackColor = P.Card, Location = new Point(24, 24), Size = new Size(card.Width - 48, card.Height - 48), Anchor = AnchorStyles.Left | AnchorStyles.Top | AnchorStyles.Right | AnchorStyles.Bottom };

            var lbl1 = new Label
            {
                Text = "Este asistente instalará SIGEJUB en tu equipo.", AutoSize = true,
                Location = new Point(0, 0), MaximumSize = new Size(inner.Width, 28),
                Font = new Font("Segoe UI", 11), ForeColor = P.Text, Anchor = AnchorStyles.Left | AnchorStyles.Top | AnchorStyles.Right
            };
            var lbl2 = new Label
            {
                Text = "Durante la instalación:", AutoSize = true,
                Location = new Point(0, 36), MaximumSize = new Size(inner.Width, 24),
                Font = new Font("Segoe UI", 10, FontStyle.Bold), ForeColor = P.Navy, Anchor = AnchorStyles.Left | AnchorStyles.Top | AnchorStyles.Right
            };
            inner.Controls.Add(lbl1); inner.Controls.Add(lbl2);

            string[] puntos = {
                "• Se copiarán los archivos de la aplicación a la carpeta de tu elección.",
                "• Se revisará tu equipo: si falta PHP, Composer o un servidor de base de datos, se instalarán automáticamente.",
                "• Con internet se descargará la última versión de lo que falte; sin internet (offline) se usarán los paquetes de la carpeta 'paquetes' junto al instalador.",
                "• Se configurará la base de datos (SQLite, MySQL/MariaDB o PostgreSQL).",
                "• Se instalará un acceso directo en el escritorio.",
                "• Se generará una URL local para comenzar a usar el sistema."
            };
            int y = 66;
            foreach (var pt in puntos)
            {
                var l = new Label { Text = pt, AutoSize = true, Location = new Point(0, y), MaximumSize = new Size(inner.Width, 24), Font = new Font("Segoe UI", 10), ForeColor = P.Muted, Anchor = AnchorStyles.Left | AnchorStyles.Top | AnchorStyles.Right };
                inner.Controls.Add(l); y += 28;
            }

            var req = new Label
            {
                Text = "Requisitos: ninguno. El instalador detecta e instala todo lo necesario (PHP 8.2+, Composer y BD).",
                AutoSize = true, Location = new Point(0, y + 12), MaximumSize = new Size(inner.Width, 40),
                Font = new Font("Segoe UI", 9, FontStyle.Italic), ForeColor = Color.FromArgb(180, 83, 9), Anchor = AnchorStyles.Left | AnchorStyles.Top | AnchorStyles.Right
            };
            inner.Controls.Add(req);

            card.Controls.Add(inner);
            currentPage.Controls.Add(card);
        }

        // ─── PÁGINA 1: CARPETA ───
        private void BuildFolder()
        {
            var h = MakeHeader("Elige dónde instalar", "Selecciona la carpeta donde se copiará la aplicación.");
            currentPage.Controls.Add(h);

            var card = new Panel { BackColor = P.Card, Location = new Point(18, 96), Size = new Size(ContentW - 36, ContentH - 176), Anchor = AnchorStyles.Left | AnchorStyles.Top | AnchorStyles.Right | AnchorStyles.Bottom };
            card.Paint += (s, e) => { using (var p = new Pen(P.Border, 1)) e.Graphics.DrawRectangle(p, 0, 0, card.Width - 1, card.Height - 1); };

            var lbl = new Label
            {
                Text = "Carpeta de instalación:", Location = new Point(24, 24), Size = new Size(200, 22),
                Font = new Font("Segoe UI", 10, FontStyle.Bold), ForeColor = P.Navy
            };
            txtPath = new TextBox
            {
                Text = installPath, Location = new Point(24, 52), Size = new Size(card.Width - 150, 30),
                Font = new Font("Segoe UI", 10), BorderStyle = BorderStyle.FixedSingle, Anchor = AnchorStyles.Left | AnchorStyles.Top | AnchorStyles.Right
            };
            txtPath.TextChanged += (s, e) => { installPath = txtPath.Text.Trim(); };
            var btnBrowse = new RoundBtn { Text = "Examinar...", IsPrimary = false, Width = 96, Height = 32, Location = new Point(card.Width - 122, 50), Font = new Font("Segoe UI", 9), Anchor = AnchorStyles.Top | AnchorStyles.Right };
            btnBrowse.BackColor = P.Bg; btnBrowse.ForeColor = P.Navy;
            btnBrowse.Click += (s, e) => {
                using (var fbd = new FolderBrowserDialog())
                {
                    fbd.Description = "Selecciona la carpeta de instalación de SIGEJUB";
                    fbd.SelectedPath = Directory.Exists(installPath) ? installPath : Environment.GetFolderPath(Environment.SpecialFolder.ProgramFilesX86);
                    if (fbd.ShowDialog(this) == DialogResult.OK)
                    {
                        installPath = Path.Combine(fbd.SelectedPath, "SIGEJUB");
                        txtPath.Text = installPath;
                    }
                }
            };

            var lblSpace = new Label
            {
                Text = "La carpeta destino se creará automáticamente si no existe.", AutoSize = false,
                Location = new Point(24, 92), Size = new Size(400, 20), Font = new Font("Segoe UI", 9), ForeColor = P.Muted, Anchor = AnchorStyles.Left | AnchorStyles.Top | AnchorStyles.Right
            };

            chkShortcut = new CheckBox
            {
                Text = "Crear acceso directo en el escritorio",
                Location = new Point(24, 140), Size = new Size(280, 24), Checked = true,
                Font = new Font("Segoe UI", 10), ForeColor = P.Text
            };
            chkShortcut.CheckedChanged += (s, e) => createShortcut = chkShortcut.Checked;

            card.Controls.AddRange(new Control[] { lbl, txtPath, btnBrowse, lblSpace, chkShortcut });
            currentPage.Controls.Add(card);
        }

        private bool ValidateFolder()
        {
            if (string.IsNullOrWhiteSpace(installPath)) { MessageBox.Show("Ingresa una carpeta de instalación válida.", "SIGEJUB", MessageBoxButtons.OK, MessageBoxIcon.Warning); return false; }
            try
            {
                string full = Path.GetFullPath(installPath);
                var di = Directory.CreateDirectory(full);
                File.WriteAllText(Path.Combine(di.FullName, ".sigejub_test.tmp"), "ok");
                File.Delete(Path.Combine(di.FullName, ".sigejub_test.tmp"));
                return true;
            }
            catch (Exception ex)
            {
                MessageBox.Show("No se puede escribir en esa carpeta:\n" + ex.Message, "SIGEJUB", MessageBoxButtons.OK, MessageBoxIcon.Error);
                return false;
            }
        }

        // ─── PÁGINA 2: BD ───
        private void BuildDb()
        {
            var h = MakeHeader("Configuración de la base de datos", "Indica el gestor y las credenciales de tu servidor de BD.");
            currentPage.Controls.Add(h);

            var card = new Panel { BackColor = P.Card, Location = new Point(18, 96), Size = new Size(ContentW - 36, ContentH - 176), Anchor = AnchorStyles.Left | AnchorStyles.Top | AnchorStyles.Right | AnchorStyles.Bottom };
            card.Paint += (s, e) => { using (var p = new Pen(P.Border, 1)) e.Graphics.DrawRectangle(p, 0, 0, card.Width - 1, card.Height - 1); };

            var lblEngine = new Label { Text = "Gestor de base de datos:", Location = new Point(24, 24), Size = new Size(220, 22), Font = new Font("Segoe UI", 10, FontStyle.Bold), ForeColor = P.Navy };
            rdoMysql = new RadioButton { Text = "MySQL / MariaDB", Location = new Point(24, 52), Size = new Size(150, 24), Checked = dbEngine == "mysql", Font = new Font("Segoe UI", 10) };
            rdoPgsql = new RadioButton { Text = "PostgreSQL", Location = new Point(178, 52), Size = new Size(130, 24), Checked = dbEngine == "pgsql", Font = new Font("Segoe UI", 10) };
            rdoSqlite = new RadioButton { Text = "SQLite (local)", Location = new Point(312, 52), Size = new Size(130, 24), Checked = dbEngine == "sqlite", Font = new Font("Segoe UI", 10) };
            rdoMysql.CheckedChanged += (s, e) => { if (rdoMysql.Checked) { dbEngine = "mysql"; if (txtPort != null) { txtPort.Text = "3306"; txtUser.Text = "root"; txtPass.Text = ""; } SetDbFieldsState(); } };
            rdoPgsql.CheckedChanged += (s, e) => { if (rdoPgsql.Checked) { dbEngine = "pgsql"; if (txtPort != null) { txtPort.Text = "5432"; txtUser.Text = "postgres"; } SetDbFieldsState(); } };
            rdoSqlite.CheckedChanged += (s, e) => { if (rdoSqlite.Checked) { dbEngine = "sqlite"; SetDbFieldsState(); } };

            int y = 96;
            var lblHost = new Label { Text = "Host:", Location = new Point(24, y + 4), Size = new Size(120, 22), ForeColor = P.Muted };
            txtHost = new TextBox { Text = dbHost, Location = new Point(150, y), Size = new Size(card.Width - 190, 26), BorderStyle = BorderStyle.FixedSingle, Anchor = AnchorStyles.Left | AnchorStyles.Top | AnchorStyles.Right };
            y += 34;
            var lblPort = new Label { Text = "Puerto:", Location = new Point(24, y + 4), Size = new Size(120, 22), ForeColor = P.Muted };
            txtPort = new TextBox { Text = dbPort, Location = new Point(150, y), Size = new Size(120, 26), BorderStyle = BorderStyle.FixedSingle };
            y += 34;
            var lblDb = new Label { Text = "Nombre de la BD:", Location = new Point(24, y + 4), Size = new Size(120, 22), ForeColor = P.Muted };
            txtDb = new TextBox { Text = dbName, Location = new Point(150, y), Size = new Size(card.Width - 190, 26), BorderStyle = BorderStyle.FixedSingle, Anchor = AnchorStyles.Left | AnchorStyles.Top | AnchorStyles.Right };
            y += 34;
            var lblUser = new Label { Text = "Usuario:", Location = new Point(24, y + 4), Size = new Size(120, 22), ForeColor = P.Muted };
            txtUser = new TextBox { Text = dbUser, Location = new Point(150, y), Size = new Size(card.Width - 190, 26), BorderStyle = BorderStyle.FixedSingle, Anchor = AnchorStyles.Left | AnchorStyles.Top | AnchorStyles.Right };
            y += 34;
            var lblPass = new Label { Text = "Clave:", Location = new Point(24, y + 4), Size = new Size(120, 22), ForeColor = P.Muted };
            txtPass = new TextBox { Text = dbPass, Location = new Point(150, y), Size = new Size(card.Width - 190, 26), BorderStyle = BorderStyle.FixedSingle, PasswordChar = '*' , Anchor = AnchorStyles.Left | AnchorStyles.Top | AnchorStyles.Right };
            y += 30;

            var nota = new Label
            {
                Text = "SQLite no requiere servidor (archivo local). Con MySQL/PostgreSQL, si el servidor no está\nactivo, el instalador descargará e instalarás uno local automáticamente.",
                AutoSize = false, Location = new Point(24, y + 6), Size = new Size(card.Width - 60, 40),
                Font = new Font("Segoe UI", 9, FontStyle.Italic), ForeColor = Color.FromArgb(180, 83, 9), Anchor = AnchorStyles.Left | AnchorStyles.Top | AnchorStyles.Right
            };

            card.Controls.AddRange(new Control[] { lblEngine, rdoMysql, rdoPgsql, rdoSqlite, lblHost, txtHost, lblPort, txtPort, lblDb, txtDb, lblUser, txtUser, lblPass, txtPass, nota });
            currentPage.Controls.Add(card);
            SetDbFieldsState();
        }

        private void SetDbFieldsState()
        {
            bool server = dbEngine == "mysql" || dbEngine == "pgsql";
            if (txtHost != null && !txtHost.IsDisposed) txtHost.Enabled = server;
            if (txtPort != null && !txtPort.IsDisposed) txtPort.Enabled = server;
            if (txtDb != null && !txtDb.IsDisposed) txtDb.Enabled = server;
            if (txtUser != null && !txtUser.IsDisposed) txtUser.Enabled = server;
            if (txtPass != null && !txtPass.IsDisposed) txtPass.Enabled = server;
        }

        private void ReadDb()
        {
            if (dbEngine == "sqlite") return; // SQLite no usa credenciales
            dbHost = string.IsNullOrWhiteSpace(txtHost.Text) ? "127.0.0.1" : txtHost.Text.Trim();
            dbPort = string.IsNullOrWhiteSpace(txtPort.Text) ? (dbEngine == "pgsql" ? "5432" : "3306") : txtPort.Text.Trim();
            dbName = string.IsNullOrWhiteSpace(txtDb.Text) ? "bd-sigejub" : txtDb.Text.Trim();
            dbUser = string.IsNullOrWhiteSpace(txtUser.Text) ? (dbEngine == "pgsql" ? "postgres" : "mysql") : txtUser.Text.Trim();
            dbPass = txtPass.Text;
        }

        // ─── PÁGINA 3: INSTALACIÓN / PROGRESO ───
        private void BuildInstall()
        {
            var h = MakeHeader("Instalando SIGEJUB...", "Esto puede tardar unos minutos. No cierres la ventana.");
            currentPage.Controls.Add(h);

            logBox = new RichTextBox
            {
                Location = new Point(18, 96), Size = new Size(ContentW - 36, ContentH - 210),
                ReadOnly = true, BackColor = P.Navy, ForeColor = Color.FromArgb(226, 232, 240),
                Font = new Font("Consolas", 9.5f), BorderStyle = BorderStyle.FixedSingle,
                Anchor = AnchorStyles.Left | AnchorStyles.Top | AnchorStyles.Right | AnchorStyles.Bottom
            };
            progressBar = new ProgressBar
            {
                Location = new Point(18, ContentH - 108), Size = new Size(ContentW - 36, 24),
                Minimum = 0, Maximum = 100, Value = 0, Style = ProgressBarStyle.Continuous,
                Anchor = AnchorStyles.Left | AnchorStyles.Right | AnchorStyles.Bottom
            };
            lblStatus = new Label
            {
                Text = "Preparando...", Location = new Point(18, ContentH - 78),
                Size = new Size(ContentW - 36, 22), ForeColor = P.Muted, Font = new Font("Segoe UI", 9),
                Anchor = AnchorStyles.Left | AnchorStyles.Right | AnchorStyles.Bottom
            };
            currentPage.Controls.Add(logBox);
            currentPage.Controls.Add(progressBar);
            currentPage.Controls.Add(lblStatus);
            logBox.AppendText("  Preparando instalación en: " + installPath + "\n\n");
        }

        // ─── PÁGINA 4: FINALIZAR ───
        private void BuildDone()
        {
            var h = MakeHeader("Instalación completada", "SIGEJUB ha sido instalado correctamente.");
            currentPage.Controls.Add(h);

            var card = new Panel { BackColor = P.Card, Location = new Point(18, 96), Size = new Size(ContentW - 36, ContentH - 176), Anchor = AnchorStyles.Left | AnchorStyles.Top | AnchorStyles.Right | AnchorStyles.Bottom };
            card.Paint += (s, e) => { using (var p = new Pen(P.Border, 1)) e.Graphics.DrawRectangle(p, 0, 0, card.Width - 1, card.Height - 1); };

            var check = new Label
            {
                Text = "\u2713", Font = new Font("Segoe UI", 44, FontStyle.Bold),
                ForeColor = P.Green, Location = new Point(24, 24), Size = new Size(80, 80),
                TextAlign = ContentAlignment.MiddleCenter
            };
            lblDoneUrl = new Label
            {
                Text = "El sistema quedó instalado en:\n" + installPath, AutoSize = false,
                Location = new Point(120, 40), Size = new Size(card.Width - 160, 50),
                Font = new Font("Segoe UI", 11), ForeColor = P.Text
            };
            var lblUrl = new Label
            {
                Text = "", AutoSize = false, Location = new Point(24, 140), Size = new Size(card.Width - 60, 28),
                Font = new Font("Segoe UI", 12, FontStyle.Bold), ForeColor = P.Indigo
            };
            lblUrl.Text = "URL:  http://localhost:" + selectedPort;

            card.Controls.Add(check);
            card.Controls.Add(lblDoneUrl);
            card.Controls.Add(lblUrl);
            currentPage.Controls.Add(card);
        }

        private void DoStart()
        {
            // El .vbs de arranque oculta el servidor y abre el navegador; usarlo siempre
            string vbs = Path.Combine(installPath, "sigejub-start.vbs");
            if (File.Exists(vbs))
            {
                try
                {
                    string wscript = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.System), "wscript.exe");
                    Process.Start(new ProcessStartInfo(wscript, "\"" + vbs + "\"") { WorkingDirectory = installPath });
                    this.Close();
                    return;
                }
                catch { }
            }

            // Fallback: arrancar BD y PHP directamente y abrir el navegador
            try
            {
                if (dbPortable && !string.IsNullOrEmpty(dbStartCmd)) StartPortableDbBackground();
                string php = phpExe;
                if (!string.IsNullOrEmpty(php) && File.Exists(php))
                {
                    string pub = Path.Combine(installPath, "public");
                    Process.Start(new ProcessStartInfo
                    {
                        FileName = php,
                        Arguments = "-S 127.0.0.1:" + selectedPort + " -t \"" + pub + "\"",
                        WorkingDirectory = installPath,
                        UseShellExecute = false
                    });
                }
            }
            catch { }
            try { Process.Start("http://localhost:" + selectedPort); } catch { }
            this.Close();
        }

        // Reinicia (o inicia) la instalación bloqueando el botón Atrás para evitar que
        // el usuario navegue a otra página mientras el hilo de instalación corre.
        private void StartInstall()
        {
            if (installing) return;
            installing = true;
            btnBack.Visible = false;
            btnBack.Enabled = false;
            btnNext.Visible = false;
            btnNext.Enabled = false;
            btnNext.Text = "Instalando...";
            RepositionButtons();
            Thread th = new Thread(() => RunInstall());
            th.IsBackground = true;
            th.Start();
        }

        private void RunInstall()
        {
            try
            {
                AppendLog("Iniciando instalación en: " + installPath, Color.Yellow);
                SetProgress(2);

                // ── 1: Copiar archivos de la app desde la variante windows/<motor> ──
                AppendLog("[1/13] Copiando archivos de la aplicación (" + GetAppSource() + ")...", Color.Yellow);
                CopyDirectory(GetAppSource(), installPath, true);
                CleanStorage(installPath);
                SetProgress(6);

                // ── 2: Detectar internet (modo online / offline) ──
                AppendLog("[2/13] Comprobando conexión a internet...", Color.Yellow);
                online = HasInternet();
                AppendLog(online
                    ? "[OK] Conexión disponible: se descargará la última versión de lo que falte."
                    : "[AVISO] Sin internet: se usarán los paquetes locales de la carpeta 'paquetes' (modo offline).",
                    online ? Color.Green : Color.Orange);
                SetProgress(9);

                // ── Pre: garantizar el runtime VC++ x64 (lo necesita PHP portable) ──
                EnsureVCpp();

                // ── 3: PHP (del sistema o portable local) ──
                AppendLog("[3/13] Verificando PHP...", Color.Yellow);
                if (!EnsurePhp())
                {
                    AppendLog("[ERROR] No se pudo obtener PHP 8.2+. Con internet se descarga solo; sin internet, coloca el zip de PHP en la carpeta 'paquetes' junto al instalador.", Color.Red);
                    FinishWithError(); return;
                }
                AppendLog("[OK] PHP: " + phpExe + (phpPortable ? " (portable interno)" : " (del sistema)"), Color.Green);
                SetProgress(14);

                // ── 4: Composer (sistema o composer.phar local) ──
                AppendLog("[4/13] Verificando Composer...", Color.Yellow);
                if (!EnsureComposer())
                {
                    AppendLog("[ERROR] No se pudo obtener Composer.", Color.Red);
                    FinishWithError(); return;
                }
                AppendLog("[OK] Composer: " + (composerPhar != null ? "composer.phar local" : "del sistema"), Color.Green);
                SetProgress(18);

                // ── 5: Base de datos ──
                AppendLog("[5/13] Preparando base de datos (" + DbEngineName() + ")...", Color.Yellow);
                if (!ResolveDatabase())
                {
                    AppendLog("[ERROR] No se pudo preparar la base de datos.", Color.Red);
                    FinishWithError(); return;
                }
                SetProgress(26);

                // ── 6: .env ──
                AppendLog("[6/13] Configurando .env...", Color.Yellow);
                if (!WriteEnvFile())
                {
                    FinishWithError(); return;
                }
                AppendLog("[OK] BD configurada en .env (" + dbEngine + ")", Color.Green);
                SetProgress(34);

                // ── 7: composer install ──
AppendLog("[7/13] Instalando dependencias (esto puede tardar)...", Color.Yellow);
                try
                {
                    string vdir = Path.Combine(installPath, "vendor");
                    if (Directory.Exists(vdir)) { AppendLog("  Limpiando vendor previo (instalación fallida previa)...", Color.Gray); Directory.Delete(vdir, true); }
                }
                catch { }
                string output;
                if (!RunComposer("install --no-interaction --no-ansi --no-progress", out output))
                {
                    AppendLog("[ERROR] composer install: " + Truncate(output, 300) + " (php=" + phpExe + ", composer=" + (composerPhar ?? composerCmd ?? "PATH") + ")", Color.Red);
                    FinishWithError(); return;
                }
                if (!File.Exists(Path.Combine(installPath, "vendor", "autoload.php")))
                {
                    AppendLog("[ERROR] composer install terminó pero NO generó vendor/autoload.php. Respuesta de composer:\n" + Truncate(output, 400), Color.Red);
                    FinishWithError(); return;
                }
                AppendLog("[OK] Dependencias instaladas", Color.Green);
                SetProgress(52);

                // ── 8: key ──
                AppendLog("[8/13] Generando APP_KEY...", Color.Yellow);
                if (!RunPhp("artisan key:generate --force --no-ansi", out output))
                {
                    AppendLog("[ERROR] key:generate: " + Truncate(output, 300), Color.Red);
                    FinishWithError(); return;
                }
                AppendLog("[OK] APP_KEY generada", Color.Green);
                SetProgress(62);

                // ── 9: migrar ──
                AppendLog("[9/13] Ejecutando migraciones...", Color.Yellow);
                if (!RunPhp("artisan migrate --force --no-ansi", out output))
                {
                    AppendLog("[ERROR] Migraciones: " + Truncate(output, 300), Color.Red);
                    AppendLog("[ERROR] Revisa que la base de datos exista y las credenciales sean correctas.", Color.Red);
                    FinishWithError(); return;
                }
                AppendLog("[OK] Migraciones ejecutadas", Color.Green);
                SetProgress(74);

                // ── 10: finalizar ──
                AppendLog("[10/13] Finalizando...", Color.Yellow);
                RunPhp("artisan optimize:clear --no-ansi", out output);
                string pubStorage = Path.Combine(installPath, "public", "storage");
                if (Directory.Exists(pubStorage)) Directory.Delete(pubStorage, true);
                RunPhp("artisan storage:link --no-ansi", out output);
                SetProgress(82);

                selectedPort = FindFreePort();
                string envPath = Path.Combine(installPath, ".env");
                string env = File.ReadAllText(envPath, Encoding.UTF8);
                env = ReplaceEnv(env, "APP_URL", "http://localhost:" + selectedPort);
                File.WriteAllText(envPath, env, Utf8NoBom);
                SetProgress(88);

                // ── 11: acceso directo ──
                if (createShortcut) CreateShortcut(installPath);
                SetProgress(95);

                // ── 12: vbs de arranque ──
                WriteLaunchVbs(installPath);

                // ── 13: desinstalador + registro "Agregar o quitar programas" ──
                WriteUninstaller(installPath);
                RegisterUninstall(installPath);
                SetProgress(100);

                AppendLog("", Color.White);
                AppendLog("  \u2713 INSTALACIÓN COMPLETADA", Color.Cyan);
                AppendLog("  PHP:    " + phpExe, Color.Cyan);
                AppendLog("  BD:     " + (dbEngine == "sqlite" ? "SQLite (archivo local)"
                            : dbPortable ? DbEngineName() + " local 127.0.0.1:" + dbPort
                            : DbEngineName() + " " + dbHost + ":" + dbPort), Color.Cyan);
                AppendLog("  Puerto: " + selectedPort, Color.Cyan);
                AppendLog("  URL:    http://localhost:" + selectedPort, Color.Cyan);
                AppendLog("  Desinstalar: uninstall.vbs (en Agregar o quitar programas)", Color.Cyan);

                this.Invoke(new Action(() =>
                {
                    installing = false;
                    Go(STEP_DONE);
                }));
            }
            catch (Exception ex)
            {
                AppendLog("[ERROR] " + ex.Message, Color.Red);
                FinishWithError();
            }
        }

        // Origen de los archivos de la app: usa la variante windows/<motor> cuando
        // existe (postgresql para pgsql, mysql como base de sqlite); si no, la raíz.
        private string GetAppSource()
        {
            string variant = dbEngine == "pgsql" ? "postgresql" : dbEngine == "sqlite" ? "mysql" : dbEngine;
            if (!string.IsNullOrEmpty(variant))
            {
                string varDir = Path.Combine(SourceRoot, "windows", variant);
                if (File.Exists(Path.Combine(varDir, "artisan"))) return varDir;
            }
            return SourceRoot;
        }

        private string DbEngineName()
        {
            if (dbEngine == "sqlite") return "SQLite";
            if (dbEngine == "pgsql") return "PostgreSQL";
            return "MySQL/MariaDB";
        }

        private void FinishWithError()
        {
            AppendLog("[ERROR] Instalación cancelada.", Color.Red);
            SetStatus("Error durante la instalación");
            this.Invoke(new Action(() =>
            {
                installing = false;
                btnBack.Visible = true;
                btnBack.Enabled = true;
                btnNext.Visible = true;
                btnNext.Enabled = true;
                btnNext.Text = "Reintentar";
                RepositionButtons();
            }));
        }

        // ─── Detección de internet (modo online / offline) ───
        private bool HasInternet()
        {
            try
            {
                ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls | SecurityProtocolType.Tls11 | SecurityProtocolType.Tls12;
                var req = (HttpWebRequest)WebRequest.Create("https://www.php.net/");
                req.Method = "HEAD";
                req.Timeout = 8000;
                using (var resp = (HttpWebResponse)req.GetResponse()) return resp.StatusCode == HttpStatusCode.OK;
            }
            catch { return false; }
        }

        private bool DownloadFile(string url, string dest)
        {
            for (int attempt = 0; attempt < 2; attempt++)
            {
                try
                {
                    ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls | SecurityProtocolType.Tls11 | SecurityProtocolType.Tls12;
                    using (var wc = new WebClient())
                    {
                        wc.Headers[HttpRequestHeader.UserAgent] = "SIGEJUB-Installer";
                        wc.DownloadFile(url, dest);
                    }
                    if (new FileInfo(dest).Length > 0) return true;
                }
                catch { try { if (File.Exists(dest)) File.Delete(dest); } catch { } }
                Thread.Sleep(700);
            }
            return false;
        }

        // Verifica que el archivo sea un ZIP válido (abre el índice central sin extraer)
        private bool IsValidZip(string path)
        {
            try { using (var z = ZipFile.OpenRead(path)) { int n = z.Entries.Count; } return true; }
            catch { return false; }
        }

        // Busca un archivo exacto en la carpeta 'paquetes' (junto al exe / raíz de la app)
        private string GetBundle(string fileName)
        {
            foreach (var b in BundleCandidates())
            {
                string p = Path.Combine(b, fileName);
                if (File.Exists(p)) return p;
            }
            return null;
        }

        // Busca un archivo por patrón (p.ej. php-*.zip) en la carpeta 'paquetes'
        private string FindBundleZip(string pattern)
        {
            foreach (var b in BundleCandidates())
            {
                if (!Directory.Exists(b)) continue;
                try
                {
                    string[] files = Directory.GetFiles(b, pattern);
                    if (files.Length == 0) continue;
                    Array.Sort(files);
                    return files[files.Length - 1];
                }
                catch { }
            }
            return null;
        }

        private string[] BundleCandidates()
        {
            return new string[]
            {
                Path.Combine(Application.StartupPath, "paquetes"),
                Path.Combine(SourceRoot, "paquetes"),
                Path.GetFullPath(Path.Combine(SourceRoot, "..", "paquetes")),
                Path.Combine(Application.StartupPath, "compont"),
                Path.Combine(SourceRoot, "compont"),
                Path.GetFullPath(Path.Combine(SourceRoot, "..", "compont"))
            };
        }

        // Busca un .exe/.msi por nombre en las carpetas de paquetes (p.ej. vc_redist*.exe)
        private string FindBundleExe(string pattern)
        {
            foreach (var b in BundleCandidates())
            {
                if (!Directory.Exists(b)) continue;
                try
                {
                    string[] files = Directory.GetFiles(b, pattern);
                    if (files.Length > 0) return files[0];
                }
                catch { }
            }
            return null;
        }

        // ─── Runtime VC++ x64 ───
        // PHP portable de windows.php.net requiere el redistribuible VC++ 2015-2022.
        private bool EnsureVCpp()
        {
            if (VCppInstalled()) return true;
            AppendLog("  Runtime VC++ 2015-2022 (x64) no detectado. Buscando vc_redist...", Color.Orange);
            string redist = FindBundleExe("vc_redist*.exe");
            if (redist == null)
            {
                AppendLog("  No hay vc_redist en 'paquetes'/'compont'. Si PHP no arranca, instala el runtime VC++ x64.", Color.Orange);
                return false;
            }
            AppendLog("  Instalando " + Path.GetFileName(redist) + " (silencioso)...", Color.White);
            RunHidden(redist, "/install /quiet /norestart");
            if (VCppInstalled()) { AppendLog("[OK] Runtime VC++ instalado.", Color.Green); return true; }
            AppendLog("[AVISO] No se pudo confirmar la instalación del runtime VC++.", Color.Orange);
            return false;
        }

        private bool VCppInstalled()
        {
            try
            {
                using (var k = Microsoft.Win32.Registry.LocalMachine.OpenSubKey(@"SOFTWARE\Microsoft\VisualStudio\14.0\VC\Runtimes\X64"))
                {
                    if (k == null) return false;
                    object v = k.GetValue("Installed");
                    return v != null && Convert.ToInt32(v) == 1;
                }
            }
            catch { return false; }
        }

        private void ExtractZip(string zip, string dest)
        {
            Directory.CreateDirectory(dest);
            // ZipFile.ExtractToDirectory NO sobreescribe: si quedó una extracción parcial de un
            // intento anterior, falla con "el archivo ya existe". Se limpia el destino primero.
            string[] existing;
            try { existing = Directory.GetFileSystemEntries(dest); }
            catch { existing = new string[0]; }
            if (existing.Length > 0)
            {
                AppendLog("  Limpiando extracción previa en " + dest + " ...", Color.White);
                foreach (var e in existing)
                {
                    try
                    {
                        if (Directory.Exists(e)) Directory.Delete(e, true);
                        else File.Delete(e);
                    }
                    catch (Exception ex) { AppendLog("[AVISO] No se pudo borrar " + Path.GetFileName(e) + ": " + ex.Message, Color.Orange); }
                }
            }
            ZipFile.ExtractToDirectory(zip, dest);
        }

        private string FindFileRecursive(string root, string name)
        {
            try { foreach (var f in Directory.GetFiles(root, name, SearchOption.AllDirectories)) return f; } catch { }
            return null;
        }

        // Lee la página de PHP.net y devuelve el nombre del zip más reciente (prefiere la serie 8.4)
        private string FindLatestPhpZipName()
        {
            try
            {
                ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls | SecurityProtocolType.Tls11 | SecurityProtocolType.Tls12;
                string html;
                using (var wc = new WebClient())
                {
                    wc.Headers[HttpRequestHeader.UserAgent] = "SIGEJUB-Installer";
                    html = wc.DownloadString(PHP_PAGE);
                }
                var rx = new System.Text.RegularExpressions.Regex("php-8\\.[0-9]+\\.[0-9]+-nts-Win32-vs[0-9]+-x64\\.zip");
                var names = new List<string>();
                foreach (System.Text.RegularExpressions.Match m in rx.Matches(html))
                    if (!names.Contains(m.Value)) names.Add(m.Value);
                var p84 = names.FindAll(n => n.StartsWith("php-8.4."));
                var cand = p84.Count > 0 ? p84 : names;
                if (cand.Count == 0) return null;
                cand.Sort();
                return cand[cand.Count - 1];
            }
            catch { return null; }
        }

        // Detecta un PHP 8.2+ en el PATH del sistema; null si no hay compatible
        private string LocateSystemPhp()
        {
            try
            {
                string outS;
                if (RunCmd("where", "php", out outS))
                {
                    if (!string.IsNullOrWhiteSpace(outS))
                    {
                        string line = outS.Split('\n')[0].Trim();
                        if (File.Exists(line))
                        {
                            string ver = "";
                            RunCmdIn(line, "-r \"echo PHP_VERSION;\"", SourceRoot, out ver);
                            ver = (ver ?? "").Trim();
                            if (IsPhpCompatible(ver)) return line;
                            AppendLog("  PHP del sistema (" + ver + ") es menor a 8.2; se instalará uno portable.", Color.Orange);
                        }
                    }
                }
            }
            catch { }
            return null;
        }

        // Devuelve las extensiones PHP obligatorias que faltan en el php indicado
        private List<string> MissingPhpExts(string phpPath)
        {
            var miss = new List<string>();
            string mods = "";
            try { RunCmdIn(phpPath, "-m", SourceRoot, out mods); } catch { }
            mods = (mods ?? "").ToLowerInvariant();
            if (string.IsNullOrWhiteSpace(mods)) { miss.Add("(php no responde)"); return miss; }
            string[] need = { "zip", "mbstring", "openssl", "curl", "dom", "fileinfo", "pdo_sqlite", "pdo_mysql", "pdo_pgsql" };
            foreach (var n in need) if (!mods.Contains(n)) miss.Add(n);
            return miss;
        }

        // Garantiza un PHP 8.2+ usable: del sistema o portable local descomprimido en la app
        private bool EnsurePhp()
        {
            string sys = LocateSystemPhp();
            if (sys != null)
            {
                var missing = MissingPhpExts(sys);
                if (missing.Count == 0)
                {
                    phpExe = sys;
                    phpPortable = false;
                    return true;
                }
                AppendLog("  El PHP del sistema no tiene las extensiones necesarias (" + string.Join(", ", missing.ToArray()) + ").", Color.Orange);
                AppendLog("  Se instalará PHP portable con todas las extensiones.", Color.Orange);
            }

            AppendLog("  PHP no disponible con las extensiones necesarias. Buscando PHP portable...", Color.White);
            string zipPath = null;
            if (online)
            {
                string name = FindLatestPhpZipName();
                if (string.IsNullOrEmpty(name))
                {
                    AppendLog("  No se pudo consultar la última versión de PHP; se usará el paquete local.", Color.Orange);
                }
                else
                {
                    AppendLog("  Descargando " + name + " ...", Color.White);
                    string tmp = Path.Combine(Path.GetTempPath(), "sigejub-" + name);
                    if (DownloadFile(PHP_PAGE + name, tmp) && IsValidZip(tmp)) zipPath = tmp;
                    else
                    {
                        try { if (File.Exists(tmp)) File.Delete(tmp); } catch { }
                        AppendLog("[AVISO] No se pudo descargar un ZIP válido de PHP; se usará el paquete local.", Color.Orange);
                    }
                }
            }
            if (zipPath == null)
            {
                zipPath = FindBundleZip("php-*.zip");
                if (zipPath != null) AppendLog("  Usando paquete offline: " + Path.GetFileName(zipPath), Color.White);
            }
            if (zipPath != null && !IsValidZip(zipPath))
            {
                AppendLog("[ERROR] El paquete local de PHP está corrupto: " + zipPath, Color.Red);
                return false;
            }
            if (zipPath == null) return false;

            string phpDir = Path.Combine(installPath, "php");
            try { ExtractZip(zipPath, phpDir); }
            catch (Exception ex) { AppendLog("[ERROR] No se pudo descomprimir PHP: " + ex.Message, Color.Red); return false; }

            string exe = FindFileRecursive(phpDir, "php.exe");
            if (exe == null) { AppendLog("[ERROR] php.exe no encontrado en el paquete de PHP.", Color.Red); return false; }

            try
            {
                string iniDir = Path.GetDirectoryName(exe);
                WritePortablePhpIni(Path.Combine(iniDir, "php.ini"));
            }
            catch (Exception ex) { AppendLog("[AVISO] No se pudo escribir php.ini: " + ex.Message, Color.Orange); }

            // Eliminar el zip temporal de internet si aplica
            try { if (Path.GetDirectoryName(Path.GetFullPath(zipPath)) == Path.GetTempPath() && File.Exists(zipPath)) File.Delete(zipPath); } catch { }

            phpExe = exe;
            phpPortable = true;

            // Verificar que php.exe realmente arranca (si falta el runtime VC++, no responde)
            string verOut;
            if (!RunCmdIn(exe, "-v", installPath, out verOut) || string.IsNullOrWhiteSpace(verOut))
            {
                AppendLog("  php.exe no responde. Comprobando runtime VC++...", Color.Orange);
                EnsureVCpp();
                if (!RunCmdIn(exe, "-v", installPath, out verOut) || string.IsNullOrWhiteSpace(verOut))
                {
                    AppendLog("[ERROR] php.exe no responde. Instala el runtime VC++ x64 o revisa el paquete de PHP.", Color.Red);
                    return false;
                }
            }
            return true;
        }

        // php.ini mínimo con las extensiones que SIGEJUB/Laravel necesitan
        private void WritePortablePhpIni(string iniPath)
        {
            var sb = new StringBuilder();
            sb.AppendLine("[PHP]");
            sb.AppendLine("extension_dir = \"ext\"");
            sb.AppendLine("date.timezone = \"America/Caracas\"");
            sb.AppendLine("memory_limit = 512M");
            sb.AppendLine("upload_max_filesize = 64M");
            sb.AppendLine("post_max_size = 64M");
            sb.AppendLine("max_execution_time = 300");
            sb.AppendLine("");
            sb.AppendLine("extension=curl");
            sb.AppendLine("extension=fileinfo");
            sb.AppendLine("extension=gd");
            sb.AppendLine("extension=intl");
            sb.AppendLine("extension=mbstring");
            sb.AppendLine("extension=mysqli");
            sb.AppendLine("extension=openssl");
            sb.AppendLine("extension=pdo_mysql");
            sb.AppendLine("extension=pdo_pgsql");
            sb.AppendLine("extension=pdo_sqlite");
            sb.AppendLine("extension=sqlite3");
            sb.AppendLine("extension=zip");
            File.WriteAllText(iniPath, sb.ToString(), Utf8NoBom);
        }

        // Garantiza Composer: prefiere un composer.phar REAL (descargado/local) y evita
        // los scripts shell de ComposerSetup (bin\composer) que PHP no puede ejecutar.
        private bool EnsureComposer()
        {
            // 1) Detectar un Composer del sistema "usable" (exe/bat/cmd reales) o su .phar real
            string sysCmd = null;
            string sysPhar = null;
            try
            {
                string outS;
                if (RunCmd("where", "composer", out outS) && !string.IsNullOrWhiteSpace(outS))
                {
                    string line = outS.Split('\n')[0].Trim();
                    if (!string.IsNullOrWhiteSpace(line) && File.Exists(line))
                    {
                        string ext = Path.GetExtension(line).ToLowerInvariant();
                        if (ext == ".exe" || ext == ".bat" || ext == ".cmd")
                            sysCmd = line;
                        else
                        {
                            // ComposerSetup deja bin\composer (script shell) + bin\composer.phar (real)
                            string phar = Path.Combine(Path.GetDirectoryName(line), "composer.phar");
                            if (File.Exists(phar)) { sysPhar = phar; }
                            else AppendLog("  Composer del sistema no es un ejecutable real (" + line + "); se usará composer.phar local.", Color.Orange);
                        }
                    }
                }
            }
            catch { }

            // 2) Preferir un composer.phar controlado por el instalador (determinista)
            string dest = Path.Combine(installPath, "composer.phar");
            bool ok = false;
            string origen = null;
            if (online)
            {
                AppendLog("  Descargando composer.phar (oficial) ...", Color.White);
                ok = DownloadFile(COMPOSER_URL, dest);
                if (ok) origen = "internet";
                else AppendLog("[AVISO] No se pudo descargar composer.phar; se usará el paquete local.", Color.Orange);
            }
            if (!ok)
            {
                string b = GetBundle("composer.phar");
                if (b != null)
                {
                    try { File.Copy(b, dest, true); ok = true; origen = "paquete local"; }
                    catch { AppendLog("[AVISO] No se pudo copiar composer.phar local.", Color.Orange); }
                }
            }
            if (!ok && sysPhar != null)
            {
                try { File.Copy(sysPhar, dest, true); ok = true; origen = sysPhar; }
                catch { AppendLog("[AVISO] No se pudo usar el composer.phar de ComposerSetup.", Color.Orange); }
            }
            if (!ok)
            {
                // Fallback: Composer-Setup.exe (oficial) si está en la carpeta de paquetes
                string setup = FindBundleExe("Composer-Setup.exe");
                if (setup != null)
                {
                    AppendLog("  Ejecutando " + Path.GetFileName(setup) + " (genera composer.phar local)...", Color.White);
                    RunHidden(setup, "--install-dir=\"" + installPath + "\" --filename=composer.phar --quiet --no-ansi");
                    if (File.Exists(dest)) { ok = true; origen = "Composer-Setup.exe"; }
                    else AppendLog("[AVISO] Composer-Setup.exe no generó composer.phar.", Color.Orange);
                }
            }
            if (ok && new FileInfo(dest).Length < 100 * 1024)
            {
                AppendLog("[AVISO] composer.phar parece corrupto/incompleto (" + new FileInfo(dest).Length + " bytes).", Color.Orange);
                try { File.Delete(dest); } catch { }
                ok = false;
            }
            if (ok)
            {
                composerPhar = dest;
                composerCmd = null;
                AppendLog("  Composer listo: composer.phar (" + origen + ")", Color.Green);
                return true;
            }

            // 3) Composer del sistema (exe/bat/cmd reales) como último recurso
            if (!string.IsNullOrEmpty(sysCmd))
            {
                composerCmd = sysCmd;
                composerPhar = null;
                AppendLog("  Usando Composer del sistema: " + composerCmd, Color.Green);
                return true;
            }
            return false;
        }

        private bool RunPhp(string args, out string output) { return RunCmdIn(phpExe, args, installPath, out output); }

        private bool RunComposer(string args, out string output)
        {
            // Prioridad: 1) composer.phar local (real, vía php), 2) composer del sistema (exe/bat/cmd)
            if (composerPhar != null && File.Exists(composerPhar))
                return RunCmdIn(phpExe, "\"" + composerPhar + "\" " + args, installPath, out output);
            if (!string.IsNullOrEmpty(composerCmd) && File.Exists(composerCmd))
            {
                string ext = Path.GetExtension(composerCmd).ToLowerInvariant();
                if (ext == ".bat" || ext == ".cmd")
                    return RunCmdIn("cmd.exe", "/c \"" + composerCmd + "\" " + args, installPath, out output);
                if (ext == ".exe")
                    return RunCmdIn(composerCmd, args, installPath, out output);
                return RunCmdIn(phpExe, "\"" + composerCmd + "\" " + args, installPath, out output);
            }
            return RunCmdIn("composer", args, installPath, out output);
        }

        // ─── Base de datos ───
        private bool DBTcpReachable(string host, int port)
        {
            try
            {
                using (var c = new System.Net.Sockets.TcpClient())
                {
                    var r = c.BeginConnect(host, port, null, null);
                    bool ok = r.AsyncWaitHandle.WaitOne(1500);
                    if (ok) c.EndConnect(r);
                    return ok;
                }
            }
            catch { return false; }
        }

        private bool WaitPort(int port, int seconds)
        {
            DateTime end = DateTime.Now.AddSeconds(seconds);
            while (DateTime.Now < end)
            {
                if (DBTcpReachable("127.0.0.1", port)) return true;
                Thread.Sleep(1000);
            }
            return false;
        }

        private bool ResolveDatabase()
        {
            if (dbEngine == "sqlite") return PrepareSqlite();

            int port = 0;
            int.TryParse(string.IsNullOrWhiteSpace(dbPort) ? (dbEngine == "pgsql" ? "5432" : "3306") : dbPort, out port);
            if (port <= 0) port = dbEngine == "pgsql" ? 5432 : 3306;

            AppendLog("  Comprobando servidor " + DbEngineName() + " en " + dbHost + ":" + port + " ...", Color.White);
            if (DBTcpReachable(dbHost, port))
            {
                AppendLog("[OK] Servidor " + DbEngineName() + " detectado en " + dbHost + ":" + port + " (se usarán las credenciales indicadas)", Color.Green);
                return EnsureServerDatabase(dbHost, port);
            }
            AppendLog("  Servidor no detectado. Instalando un servidor " + DbEngineName() + " local...", Color.Orange);
            return dbEngine == "pgsql" ? InstallPortablePostgres(port) : InstallPortableMariaDB(port);
        }

        // Con un servidor ya existente, conecta y crea la base de datos si no existe
        // (usando windows/verificar-bd.php, que debe ir junto al .exe).
        private bool EnsureServerDatabase(string host, int port)
        {
            string helper = Path.Combine(Application.StartupPath, "verificar-bd.php");
            if (!File.Exists(helper))
            {
                AppendLog("[AVISO] No se encontró verificar-bd.php junto al instalador; la BD '" + dbName + "' debe existir. Migrar fallará si no está.", Color.Orange);
                return true;
            }
            AppendLog("  Preparando base de datos '" + dbName + "' (se crea si no existe)...", Color.White);
            string variant = dbEngine == "pgsql" ? "pgsql" : "mysql";
            var env = new Dictionary<string, string>(StringComparer.OrdinalIgnoreCase) { { "SIGEJUB_DB_PASS", dbPass } };
            string output;
            // Args (5): <mysql|pgsql> <host> <puerto> <bd> <usuario> ; la clave va por env.
            bool ok = RunCmdIn(phpExe, "-f \"" + helper + "\" -- " + variant + " " + host + " " + port + " \"" + dbName + "\" \"" + dbUser + "\"",
                               installPath, out output, env);
            if (!ok)
            {
                AppendLog("[ERROR] No se pudo preparar la BD: " + Truncate(output, 400), Color.Red);
                AppendLog("[ERROR] Revisa las credenciales y que el servidor acepte conexiones desde 127.0.0.1.", Color.Red);
                return false;
            }
            AppendLog("[OK] " + output.Trim(), Color.Green);
            return true;
        }

        private bool PrepareSqlite()
        {
            try
            {
                string dir = Path.Combine(installPath, "database");
                Directory.CreateDirectory(dir);
                string file = Path.Combine(dir, "bd-sigejub.sqlite");
                if (!File.Exists(file)) File.WriteAllBytes(file, new byte[0]);
                dbName = file;
                dbPortable = false;
                AppendLog("[OK] Base SQLite preparada: " + file, Color.Green);
                return true;
            }
            catch (Exception ex) { AppendLog("[ERROR] SQLite: " + ex.Message, Color.Red); return false; }
        }

        private void WriteMariaDbIni(string iniPath, string baseDir, string dataDir, int port)
        {
            var sb = new StringBuilder();
            sb.AppendLine("[mysqld]");
            sb.AppendLine("basedir=\"" + baseDir + "\"");
            sb.AppendLine("datadir=\"" + dataDir + "\"");
            sb.AppendLine("port=" + port);
            sb.AppendLine("bind-address=127.0.0.1");
            sb.AppendLine("character-set-server=utf8mb4");
            sb.AppendLine("collation-server=utf8mb4_unicode_ci");
            sb.AppendLine("[client]");
            sb.AppendLine("port=" + port);
            File.WriteAllText(iniPath, sb.ToString(), Utf8NoBom);
        }

        private bool InstallPortableMariaDB(int port)
        {
            try
            {
                string zipPath = null;
                AppendLog("  Obteniendo MariaDB portable (~90 MB)...", Color.White);
                if (online)
                {
                    foreach (var url in MARIADB_URLS)
                    {
                        if (zipPath != null) break;
                        string tmp = Path.Combine(Path.GetTempPath(), "sigejub-mariadb.zip");
                        if (DownloadFile(url, tmp) && IsValidZip(tmp)) zipPath = tmp;
                        else
                        {
                            try { if (File.Exists(tmp)) File.Delete(tmp); } catch { }
                            AppendLog("[AVISO] No se pudo descargar MariaDB desde " + new Uri(url).Host + "; probando otra versión...", Color.Orange);
                        }
                    }
                }
                if (zipPath == null)
                {
                    zipPath = FindBundleZip("mariadb-*.zip");
                    if (zipPath != null) AppendLog("  Usando paquete offline: " + Path.GetFileName(zipPath), Color.White);
                }
                if (zipPath != null && !IsValidZip(zipPath))
                {
                    AppendLog("[ERROR] El paquete local de MariaDB está corrupto: " + zipPath, Color.Red);
                    return false;
                }

                if (zipPath == null)
                {
                    // Fallback: si solo hay un .msi en la carpeta de paquetes, instalarlo silenciosamente
                    string msi = FindBundleExe("mariadb-*.msi");
                    if (msi == null)
                    {
                        AppendLog("[ERROR] No hay paquete MariaDB (paquetes/mariadb-*.zip o *.msi) ni internet.", Color.Red);
                        return false;
                    }
                    AppendLog("  Instalando MariaDB desde MSI (silencioso)...", Color.White);
                    RunHidden("msiexec.exe", "/i \"" + msi + "\" /qn /norestart PORT=" + port);
                    if (!WaitPort(port, 120))
                    {
                        AppendLog("[ERROR] MariaDB (MSI) no respondió en el puerto " + port + " tras 120 s.", Color.Red);
                        return false;
                    }
                    string mysqlExe = FindMariaDbMysql();
                    if (mysqlExe == null) { AppendLog("[ERROR] mysql.exe de MariaDB (MSI) no localizado en Program Files.", Color.Red); return false; }
                    string sqlMsi = "CREATE DATABASE IF NOT EXISTS `" + dbName.Replace("`", "``") + "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
                    string outMsi;
                    if (!RunCmdIn(mysqlExe, "-h127.0.0.1 -P" + port + " -uroot -e\"" + sqlMsi + "\"", installPath, out outMsi))
                    {
                        AppendLog("[ERROR] No se pudo crear la BD: " + Truncate(outMsi, 300), Color.Red); return false;
                    }
                    dbStartCmd = ""; // el MSI registra un servicio que arranca solo en el inicio
                    dbHost = "127.0.0.1"; dbPort = port.ToString(); dbUser = "root"; dbPass = ""; dbPortable = true;
                    AppendLog("[OK] MariaDB (MSI) lista. BD '" + dbName + "' creada (usuario root, sin clave).", Color.Green);
                    return true;
                }

                string dir = Path.Combine(installPath, "mariadb");
                AppendLog("  Descomprimiendo MariaDB...", Color.White);
                ExtractZip(zipPath, dir);

                string mysqld = FindFileRecursive(dir, "mysqld.exe");
                string binDir = mysqld != null ? Path.GetDirectoryName(mysqld) : null;
                if (binDir == null) { AppendLog("[ERROR] mysqld.exe no encontrado en el paquete MariaDB.", Color.Red); return false; }
                string baseDir = Directory.GetParent(binDir).Parent.FullName;
                string dataDir = Path.Combine(dir, "data");
                Directory.CreateDirectory(dataDir);

                string ini = Path.Combine(dir, "my.ini");
                WriteMariaDbIni(ini, baseDir, dataDir, port);

                AppendLog("  Inicializando datos de MariaDB...", Color.White);
                bool initOk = false;
                string initExe = Path.Combine(binDir, "mariadb-install-db.exe");
                string initArgs = "--defaults-file=\"" + ini + "\" --datadir=\"" + dataDir + "\" --auth-root-authentication-method=normal --skip-test-db";
                if (!File.Exists(initExe))
                {
                    initExe = Path.Combine(binDir, "mysql_install_db.exe");
                    initArgs = "--defaults-file=\"" + ini + "\" --datadir=\"" + dataDir + "\"";
                }
                if (File.Exists(initExe))
                {
                    string outS;
                    initOk = RunCmdIn(initExe, initArgs, installPath, out outS);
                    if (!initOk) { AppendLog("[ERROR] " + Path.GetFileName(initExe) + ": " + Truncate(outS, 400), Color.Red); return false; }
                }
                else
                {
                    string outS;
                    initOk = RunCmdIn(mysqld, "--defaults-file=\"" + ini + "\" --initialize-insecure", installPath, out outS);
                    if (!initOk) { AppendLog("[ERROR] mysqld --initialize: " + Truncate(outS, 400), Color.Red); return false; }
                }

                AppendLog("  Arrancando MariaDB en el puerto " + port + " ...", Color.White);
                try
                {
                    var psi = new ProcessStartInfo(mysqld, "--defaults-file=\"" + ini + "\"")
                    {
                        UseShellExecute = false, CreateNoWindow = true,
                        WindowStyle = ProcessWindowStyle.Hidden, WorkingDirectory = installPath
                    };
                    Process.Start(psi);
                }
                catch (Exception ex) { AppendLog("[ERROR] No se pudo iniciar mysqld: " + ex.Message, Color.Red); return false; }

                if (!WaitPort(port, 60)) { AppendLog("[ERROR] MariaDB no respondió en el puerto " + port + " tras 60 s.", Color.Red); return false; }

                string mysql = Path.Combine(binDir, "mysql.exe");
                string sql = "CREATE DATABASE IF NOT EXISTS `" + dbName.Replace("`", "``") + "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
                string out2;
                if (!RunCmdIn(mysql, "-h127.0.0.1 -P" + port + " -uroot -e\"" + sql + "\"", installPath, out out2))
                {
                    AppendLog("[ERROR] No se pudo crear la BD: " + Truncate(out2, 300), Color.Red); return false;
                }

                dbStartCmd = "\"" + mysqld + "\" --defaults-file=\"" + ini + "\"";
                dbHost = "127.0.0.1"; dbPort = port.ToString(); dbUser = "root"; dbPass = ""; dbPortable = true;
                AppendLog("[OK] MariaDB local lista. BD '" + dbName + "' creada (usuario root, sin clave).", Color.Green);
                return true;
            }
            catch (Exception ex) { AppendLog("[ERROR] MySQL/MariaDB: " + ex.Message, Color.Red); return false; }
        }

        // Localiza mysql.exe de una instalación MariaDB vía MSI en Program Files
        private string FindMariaDbMysql()
        {
            foreach (var root in new string[] { @"C:\Program Files\MariaDB", @"C:\Program Files (x86)\MariaDB" })
            {
                if (!Directory.Exists(root)) continue;
                try
                {
                    foreach (var d in Directory.GetDirectories(root))
                    {
                        string m = Path.Combine(d, "bin", "mysql.exe");
                        if (File.Exists(m)) return m;
                    }
                }
                catch { }
            }
            return null;
        }

        private bool InstallPortablePostgres(int port)
        {
            try
            {
                string zipPath = null;
                AppendLog("  Obteniendo PostgreSQL portable (~300 MB)...", Color.White);
                if (online)
                {
                    foreach (var url in PGSQL_URLS)
                    {
                        if (zipPath != null) break;
                        string tmp = Path.Combine(Path.GetTempPath(), "sigejub-pgsql.zip");
                        if (DownloadFile(url, tmp) && IsValidZip(tmp)) zipPath = tmp;
                        else
                        {
                            try { if (File.Exists(tmp)) File.Delete(tmp); } catch { }
                            AppendLog("[AVISO] No se pudo descargar PostgreSQL desde " + new Uri(url).Host + "; probando otra versión...", Color.Orange);
                        }
                    }
                }
                if (zipPath == null)
                {
                    zipPath = FindBundleZip("postgresql-*.zip");
                    if (zipPath != null) AppendLog("  Usando paquete offline: " + Path.GetFileName(zipPath), Color.White);
                }
                if (zipPath != null && !IsValidZip(zipPath))
                {
                    AppendLog("[ERROR] El paquete local de PostgreSQL está corrupto: " + zipPath, Color.Red);
                    return false;
                }
                if (zipPath == null)
                {
                    AppendLog("[ERROR] No hay paquete PostgreSQL (paquetes/postgresql-*.zip) ni internet.", Color.Red);
                    return false;
                }

                string dir = Path.Combine(installPath, "pgsql");
                AppendLog("  Descomprimiendo PostgreSQL...", Color.White);
                ExtractZip(zipPath, dir);

                string initdb = FindFileRecursive(dir, "initdb.exe");
                string binDir = initdb != null ? Path.GetDirectoryName(initdb) : null;
                if (binDir == null) { AppendLog("[ERROR] initdb.exe no encontrado en el paquete PostgreSQL.", Color.Red); return false; }
                string dataDir = Path.Combine(dir, "data");
                Directory.CreateDirectory(dataDir);

                AppendLog("  Inicializando datos de PostgreSQL...", Color.White);
                string outS;
                if (!RunCmdIn(initdb, "-D \"" + dataDir + "\" -U postgres --auth=trust -E UTF8 --no-locale", installPath, out outS))
                {
                    AppendLog("[ERROR] initdb: " + Truncate(outS, 400), Color.Red); return false;
                }

                dbStartCmd = "\"" + Path.Combine(binDir, "pg_ctl.exe") + "\" -D \"" + dataDir + "\" -l \"" + Path.Combine(dir, "postgres.log") + "\" -o \"-p " + port + " -h 127.0.0.1\" start";
                AppendLog("  Arrancando PostgreSQL en el puerto " + port + " ...", Color.White);
                string out2;
                // pg_ctl start lanza el demonio postgres en segundo plano y termina;
                // si no se usa detach, la herencia del pipe de salida bloquea el instalador.
                if (!RunCmdIn(Path.Combine(binDir, "pg_ctl.exe"), "-D \"" + dataDir + "\" -l \"" + Path.Combine(dir, "postgres.log") + "\" -o \"-p " + port + " -h 127.0.0.1\" start", installPath, out out2, detach: true))
                {
                    AppendLog("[ERROR] pg_ctl start: " + Truncate(out2, 300), Color.Red); return false;
                }
                if (!WaitPort(port, 60)) { AppendLog("[ERROR] PostgreSQL no respondió en el puerto " + port + " tras 60 s.", Color.Red); return false; }

                // Establecer la clave del usuario postgres sin exponerla en la línea de comandos:
                // se escribe en un archivo temporal y se pasa con -f + variable PGPASSWORD.
                string psql = Path.Combine(binDir, "psql.exe");
                string pass = dbPass;
                string passSql = "ALTER USER postgres PASSWORD '" + pass.Replace("'", "''") + "';\n";
                string fSql = Path.Combine(Path.GetTempPath(), "sigejub-pg-setpass.sql");
                File.WriteAllText(fSql, passSql, Utf8NoBom);
                var env = new Dictionary<string, string> { { "PGPASSWORD", pass } };
                if (!RunCmdIn(psql, "-h127.0.0.1 -p" + port + " -U postgres -f \"" + fSql + "\"", installPath, out out2, env))
                {
                    AppendLog("[AVISO] No se pudo definir la clave de postgres: " + Truncate(out2, 200), Color.Orange);
                }
                try { File.Delete(fSql); } catch { }
                // Crear la base de datos (las comillas dobles del SQL van dobladas para CreateProcess)
                string ddl = "-h127.0.0.1 -p" + port + " -U postgres -c \"CREATE DATABASE \"\"" + dbName + "\"\"\"";
                if (!RunCmdIn(psql, ddl, installPath, out out2))
                {
                    AppendLog("[ERROR] No se pudo crear la BD: " + Truncate(out2, 300), Color.Red); return false;
                }

                dbHost = "127.0.0.1"; dbPort = port.ToString(); dbUser = "postgres"; dbPass = pass; dbPortable = true;
                AppendLog("[OK] PostgreSQL local listo. BD '" + dbName + "' creada (usuario postgres).", Color.Green);
                return true;
            }
            catch (Exception ex) { AppendLog("[ERROR] PostgreSQL: " + ex.Message, Color.Red); return false; }
        }

        // Escribe .env según el motor de BD resuelto
        private bool WriteEnvFile()
        {
            try
            {
                string envPath = Path.Combine(installPath, ".env");
                string envExample = Path.Combine(installPath, ".env.example");
                if (!File.Exists(envPath))
                {
                    if (File.Exists(envExample)) File.Copy(envExample, envPath);
                    else
                    {
                        // CopyDirectory excluye los .env*, así que se busca el ejemplo cerca del instalador;
                        // si no aparece en ningún sitio, se genera un .env mínimo (nunca fallamos aquí).
                        string srcExample = LocateEnvExample();
                        if (srcExample != null)
                        {
                            try { File.Copy(srcExample, envPath); }
                            catch (Exception ex2) { AppendLog("[ERROR] No se pudo crear .env: " + ex2.Message, Color.Red); return false; }
                        }
                        else
                        {
                            File.WriteAllText(envPath, DefaultEnvTemplate(), Utf8NoBom);
                            AppendLog("[AVISO] No se encontró .env.example; se generó un .env base.", Color.Orange);
                        }
                    }
                }
                string env = File.ReadAllText(envPath, Encoding.UTF8);
                env = RemoveEnvLine(env, "DB_CONNECTION_PGSQL"); env = RemoveEnvLine(env, "DB_HOST_PGSQL");
                env = RemoveEnvLine(env, "DB_PORT_PGSQL"); env = RemoveEnvLine(env, "DB_DATABASE_PGSQL");
                env = RemoveEnvLine(env, "DB_USERNAME_PGSQL"); env = RemoveEnvLine(env, "DB_PASSWORD_PGSQL");
                env = ReplaceEnv(env, "DB_CONNECTION", dbEngine);
                // Cookie de sesión ÚNICA por instalación: evita que varias instalaciones en el
                // mismo host (p.ej. localhost:8000 y localhost:8001) se pisen las cookies y
                // provoquen "419 Page Expired" por token CSRF descifrado con otra APP_KEY.
                env = ReplaceEnv(env, "SESSION_COOKIE", "sigejub_" + selectedPort + "_session");
                if (dbEngine == "sqlite")
                {
                    env = ReplaceEnv(env, "DB_DATABASE", dbName);
                    env = RemoveEnvLine(env, "DB_HOST"); env = RemoveEnvLine(env, "DB_PORT");
                    env = RemoveEnvLine(env, "DB_USERNAME"); env = RemoveEnvLine(env, "DB_PASSWORD");
                }
                else
                {
                    env = ReplaceEnv(env, "DB_HOST", dbHost);
                    env = ReplaceEnv(env, "DB_PORT", dbPort);
                    env = ReplaceEnv(env, "DB_DATABASE", dbName);
                    env = ReplaceEnv(env, "DB_USERNAME", dbUser);
                    env = ReplaceEnv(env, "DB_PASSWORD", dbPass);
                }
                File.WriteAllText(envPath, env, Utf8NoBom);
                return true;
            }
            catch (Exception ex) { AppendLog("[ERROR] No se pudo escribir .env: " + ex.Message, Color.Red); return false; }
        }

        // Busca .env.example en varias rutas candidatas
        private string LocateEnvExample()
        {
            string[] cands = {
                Path.Combine(SourceRoot, ".env.example"),
                Path.Combine(Application.StartupPath, ".env.example"),
                Path.GetFullPath(Path.Combine(SourceRoot, "..", ".env.example"))
            };
            foreach (var p in cands)
                if (File.Exists(p)) return p;
            return null;
        }

        // Plantilla mínima de .env (solo como último recurso)
        private string DefaultEnvTemplate()
        {
            var sb = new StringBuilder();
            sb.AppendLine("APP_NAME=SIGEJUB");
            sb.AppendLine("APP_ENV=local");
            sb.AppendLine("APP_KEY=");
            sb.AppendLine("APP_DEBUG=true");
            sb.AppendLine("APP_URL=http://localhost:8000");
            sb.AppendLine("");
            sb.AppendLine("LOG_CHANNEL=stack");
            sb.AppendLine("SESSION_DRIVER=file");
            sb.AppendLine("SESSION_COOKIE=sigejub_8000_session");
            sb.AppendLine("SESSION_LIFETIME=120");
            sb.AppendLine("CACHE_DRIVER=file");
            sb.AppendLine("QUEUE_CONNECTION=sync");
            sb.AppendLine("");
            sb.AppendLine("DB_CONNECTION=mysql");
            sb.AppendLine("DB_HOST=127.0.0.1");
            sb.AppendLine("DB_PORT=3306");
            sb.AppendLine("DB_DATABASE=bd-sigejub");
            sb.AppendLine("DB_USERNAME=root");
            sb.AppendLine("DB_PASSWORD=");
            return sb.ToString();
        }

        // Lanza un proceso en segundo plano sin esperarlo (mysqld, pg_ctl...)
        private void StartBackground(string exe, string args, string workdir)
        {
            try
            {
                Process.Start(new ProcessStartInfo(exe, args)
                {
                    UseShellExecute = false, CreateNoWindow = true,
                    WindowStyle = ProcessWindowStyle.Hidden, WorkingDirectory = workdir
                });
            }
            catch { }
        }

        // Arranca en segundo plano el servidor BD portátil instalado
        private void StartPortableDbBackground()
        {
            try
            {
                if (string.IsNullOrEmpty(dbStartCmd)) return;
                string line = dbStartCmd;
                string exe, args;
                int qi = line.IndexOf('"');
                if (qi == 0)
                {
                    int q2 = line.IndexOf('"', qi + 1);
                    if (q2 < 0) return;
                    exe = line.Substring(qi + 1, q2 - qi - 1);
                    args = line.Substring(q2 + 1).TrimStart();
                }
                else
                {
                    int sp = line.IndexOf(' ');
                    if (sp < 0) { exe = line; args = ""; }
                    else { exe = line.Substring(0, sp); args = line.Substring(sp + 1); }
                }
                StartBackground(exe, args, installPath);
            }
            catch { }
        }

        // ─── Copia de directorio (filtro de producción) ───
        // Solo se copian las carpetas y archivos esenciales de Laravel/PHP.
        // Quedan fuera artefactos de desarrollo: .git, el propio instalador,
        // tests, scripts de construcción, ejecutables, logs y documentación.
        private static readonly HashSet<string> CopyTopDirs = new HashSet<string>(StringComparer.OrdinalIgnoreCase)
        {
            "app", "bootstrap", "config", "database", "lang", "public", "resources", "routes", "storage"
        };

        private static readonly HashSet<string> CopyTopFiles = new HashSet<string>(StringComparer.OrdinalIgnoreCase)
        {
            "artisan", "composer.json", "composer.lock", ".env.example",
            "inicio.php", "start.bat", "start.sh", "detener.bat", "detener.sh",
            "sigejub-start.vbs"
        };

        // Nombres que nunca deben viajar a producción, estén donde estén.
        private static readonly HashSet<string> CopySkipNames = new HashSet<string>(StringComparer.OrdinalIgnoreCase)
        {
            ".git", ".github", ".gitignore", ".gitattributes", ".editorconfig",
            "node_modules", "installer-src", "paquetes", "vendor", "tests",
            "SIGEJUB-Installer.exe", "vbstest.exe", "build-installer.ps1",
            "descargar-paquetes.ps1", "setup.bat", "setup.sh", "phpunit.xml",
            "README.md", "INFORME_AUDITORIA.md", "bd-sigejub"
        };

        private void CopyDirectory(string src, string dst, bool topLevel)
        {
            Directory.CreateDirectory(dst);
            if (topLevel)
            {
                // Raíz: solo las carpetas de la lista blanca
                foreach (var dir in Directory.GetDirectories(src, "*", SearchOption.TopDirectoryOnly))
                {
                    string name = Path.GetFileName(dir);
                    if (!CopyTopDirs.Contains(name)) continue;
                    CopyDirectory(dir, Path.Combine(dst, name), false);
                }
                // Raíz: solo los archivos de la lista blanca
                foreach (var file in Directory.GetFiles(src, "*", SearchOption.TopDirectoryOnly))
                {
                    string name = Path.GetFileName(file);
                    if (!CopyTopFiles.Contains(name)) continue;
                    try { File.Copy(file, Path.Combine(dst, name), true); } catch { }
                }
                return;
            }
            // Subcarpetas recursivas con exclusión de nombres prohibidos
            foreach (var dir in Directory.GetDirectories(src, "*", SearchOption.TopDirectoryOnly))
            {
                string name = Path.GetFileName(dir);
                if (CopySkipNames.Contains(name)) continue;
                // public/storage se reconstruye con storage:link al final de la instalación
                if (name == "storage" && string.Equals(Path.GetFileName(src), "public", StringComparison.OrdinalIgnoreCase)) continue;
                CopyDirectory(dir, Path.Combine(dst, name), false);
            }
            foreach (var file in Directory.GetFiles(src))
            {
                string name = Path.GetFileName(file);
                if (CopySkipNames.Contains(name)) continue;
                if (name.EndsWith(".log", StringComparison.OrdinalIgnoreCase)) continue;
                try { File.Copy(file, Path.Combine(dst, name), true); } catch { }
            }
        }

        // Vacía los contenidos volátiles de storage (caché, sesiones, vistas compiladas,
        // logs y backups locales) manteniendo las carpetas para que Laravel funcione.
        private void CleanStorage(string appPath)
        {
            string[] dirs = {
                "storage\\framework\\cache\\data",
                "storage\\framework\\sessions",
                "storage\\framework\\views",
                "storage\\framework\\testing",
                "storage\\logs",
                "storage\\app\\backups",
                "storage\\app\\temp",
                "storage\\app\\private",
                "storage\\app\\private\\temp"
            };
            foreach (var rel in dirs)
            {
                string d = Path.Combine(appPath, rel);
                if (!Directory.Exists(d)) continue;
                foreach (var f in Directory.GetFiles(d)) { try { File.Delete(f); } catch { } }
                foreach (var s in Directory.GetDirectories(d)) { try { Directory.Delete(s, true); } catch { } }
            }
            // Logs sueltos en storage/logs ya cubiertos arriba; garantizar luego la
            // creación de storage/app/public para el storage:link.
            Directory.CreateDirectory(Path.Combine(appPath, "storage", "app", "public"));
        }

        private void CreateShortcut(string appPath)
        {
            try
            {
                string desktop = Environment.GetFolderPath(Environment.SpecialFolder.Desktop);
                string lnkPath = Path.Combine(desktop, "SIGEJUB.lnk");
                AppendLog("Creando acceso directo en: " + desktop, Color.Gray);

                string ico = Path.Combine(appPath, "public", "img", "imagen_2026-05-19_065531142.ico") + ", 0";
                string uninstallVbs = Path.Combine(appPath, "uninstall.vbs");
                string wscript = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.System), "wscript.exe");
                string vbsTarget = Path.Combine(appPath, "sigejub-start.vbs");

                bool created = false;
                // Preferimos el .vbs de arranque: inicia el servidor PHP OCULTO y abre el navegador.
                if (File.Exists(vbsTarget))
                {
                    created = CreateShortcutViaCom(lnkPath, wscript, "\"" + vbsTarget + "\"", appPath, 7, ico);
                }
                // Fallback: si no existe el .vbs, apuntar directamente a php.exe
                if (!created)
                {
                    string phpPath = phpExe;
                    if (!string.IsNullOrEmpty(phpPath) && File.Exists(phpPath))
                    {
                        try
                        {
                            created = CreateShortcutViaCom(lnkPath, phpPath,
                                "-S 127.0.0.1:" + selectedPort + " -t \"" + appPath + "\\public\"",
                                appPath, 1, ico);
                        }
                        catch (Exception ex) { AppendLog("[AVISO] Acceso directo (php): " + ex.Message, Color.Orange); }
                    }
                }

                // Menú Inicio: carpeta SIGEJUB con lanzador y desinstalador
                string progDir = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.Programs), "SIGEJUB");
                try { Directory.CreateDirectory(progDir); } catch { }
                if (File.Exists(vbsTarget))
                {
                    CreateShortcutViaCom(Path.Combine(progDir, "SIGEJUB.lnk"), wscript, "\"" + vbsTarget + "\"", appPath, 7, ico);
                }
                else if (!string.IsNullOrEmpty(phpExe) && File.Exists(phpExe))
                {
                    CreateShortcutViaCom(Path.Combine(progDir, "SIGEJUB.lnk"), phpExe,
                        "-S 127.0.0.1:" + selectedPort + " -t \"" + appPath + "\\public\"", appPath, 1, ico);
                }
                CreateShortcutViaCom(Path.Combine(progDir, "Desinstalar SIGEJUB.lnk"), wscript, "\"" + uninstallVbs + "\"", "C:\\Windows\\System32", 7, "shell32.dll,40");

                if (created) AppendLog("[OK] Acceso directo creado", Color.Green);
                else AppendLog("[AVISO] No se pudo crear el acceso directo en: " + lnkPath, Color.Orange);
            }
            catch (Exception ex) { AppendLog("[AVISO] Acceso directo: " + ex.Message, Color.Orange); }
        }

        // Crea un .lnk usando el objeto COM WScript.Shell directamente (sin cscript.exe ni .vbs temporal).
        // Se ejecuta en el hilo de UI (STA) porque COM requiere un apartamento de un hilo.
        private bool CreateShortcutViaCom(string lnkPath, string target, string arguments,
            string workingDir, int windowStyle, string icon)
        {
            var result = false;
            this.Invoke(new Action(() =>
            {
                try
                {
                    Type t = Type.GetTypeFromProgID("WScript.Shell");
                    if (t == null) return;
                    dynamic shell = Activator.CreateInstance(t);
                    try
                    {
                        dynamic sc = shell.CreateShortcut(lnkPath);
                        try
                        {
                            sc.TargetPath = target;
                            sc.Arguments = arguments;
                            sc.WorkingDirectory = workingDir;
                            sc.WindowStyle = windowStyle;
                            sc.Description = "SIGEJUB - Sistema de Gestion de Jubilaciones";
                            sc.IconLocation = icon;
                            sc.Save();
                            result = File.Exists(lnkPath);
                        }
                        finally { Marshal.FinalReleaseComObject(sc); }
                    }
                    finally { Marshal.FinalReleaseComObject(shell); }
                }
                catch { }
            }));
            return result;
        }

        // Obtiene la ruta completa de php.exe desde el PATH
        private string LocatePhp()
        {
            try
            {
                string outS;
                if (RunCmd("where", "php", out outS))
                {
                    if (!string.IsNullOrWhiteSpace(outS))
                    {
                        string line = outS.Split('\n')[0].Trim();
                        if (File.Exists(line)) return line;
                    }
                }
            }
            catch { }
            return null;
        }

        // Valida que la versión de PHP sea 8.2+ (formato "8.3.5", "8.2.12", etc.)
        private bool IsPhpCompatible(string v)
        {
            try
            {
                v = (v ?? "").Trim();
                int dot = v.IndexOf('.');
                if (dot < 0) return false;
                int major = int.Parse(v.Substring(0, dot));
                if (major < 8) return false;
                if (major > 8) return true;
                string rest = v.Substring(dot + 1);
                int dot2 = rest.IndexOf('.');
                string minorStr = dot2 >= 0 ? rest.Substring(0, dot2) : rest;
                int minor = int.Parse(minorStr);
                return minor >= 2;
            }
            catch { return false; }
        }

        private void WriteLaunchVbs(string appPath)
        {
            try
            {
                // Si usamos un servidor BD local, generar un .bat que lo arranque si no está activo
                bool writeDbBat = dbPortable && !string.IsNullOrEmpty(dbStartCmd);
                string dbBat = Path.Combine(appPath, "sigejub-start-db.bat");
                if (writeDbBat)
                {
                    var sb = new StringBuilder();
                    sb.AppendLine("@echo off");
                    sb.AppendLine("powershell -NoProfile -Command \"if(Test-NetConnection 127.0.0.1 -Port " + dbPort + " -InformationLevel Quiet -WarningAction SilentlyContinue){exit 0}\"");
                    sb.AppendLine("if not errorlevel 1 exit /b 0");
                    sb.AppendLine("start \"\" /b " + dbStartCmd);
                    sb.AppendLine("exit /b 0");
File.WriteAllText(dbBat, sb.ToString(), Utf8NoBom);
                }

                string php = phpExe;
                if (string.IsNullOrEmpty(php)) php = "php";

                // sigejub-start.vbs: arranca la BD (si aplica) y el servidor PHP OCULTO
                // en segundo plano, espera a que levante y abre el navegador en la página.
                string vbs = Path.Combine(appPath, "sigejub-start.vbs");
var sbv = new StringBuilder();
                sbv.AppendLine("On Error Resume Next");
                sbv.AppendLine("Set sh = CreateObject(\"WScript.Shell\")");
                sbv.AppendLine("sh.CurrentDirectory = " + VbsStr(appPath));
                if (writeDbBat) sbv.AppendLine("sh.Run " + VbsStr(dbBat) + ", 0, False");
                sbv.AppendLine("sh.Run " + VbsStr(php + " -S 127.0.0.1:" + selectedPort + " -t \"" + VbsInner(appPath + "\\public") + "\"") + ", 0, False");
                sbv.AppendLine("WScript.Sleep 2500");
                sbv.AppendLine("sh.Run " + VbsStr("http://localhost:" + selectedPort) + ", 1, False");
                File.WriteAllText(vbs, sbv.ToString(), Utf8NoBom);
            }
            catch { }
        }

        // ─── Desinstalador ───
        // Genera uninstall.vbs (orquestador gráfico) + uninstall-body.ps1 (lógica).
        // El .vbs se auto-copia a %TEMP% para poder borrar la propia carpeta de
        // instalación sin conflictos de archivos en uso.
        // Codificación ANSI (sin BOM) para scripts: cscript.exe rechaza el BOM
        // UTF-8 con "Carácter no válido" en sistemas con página de códigos 1252.
        private static readonly Encoding ScriptEncoding = Encoding.GetEncoding(1252);

        private void WriteUninstaller(string appPath)
        {
            try
            {
                string ps1 = Path.Combine(appPath, "uninstall-body.ps1");
                File.WriteAllText(ps1, UninstallPsBody(), ScriptEncoding);

                string vbsContent = UninstallVbsTemplate()
                    .Replace("@@APPFOLDER@@", appPath)
                    .Replace("@@SERVERPORT@@", selectedPort.ToString())
                    .Replace("@@DBPORT@@", dbPort)
                    .Replace("@@DBPORTABLE@@", dbPortable ? "1" : "0");
                File.WriteAllText(Path.Combine(appPath, "uninstall.vbs"), vbsContent, ScriptEncoding);
            }
            catch (Exception ex) { AppendLog("[AVISO] Desinstalador: " + ex.Message, Color.Orange); }
        }

        private string UninstallVbsTemplate()
        {
            return
                "' SIGEJUB - Desinstalador (generado por el instalador)\n" +
                "On Error Resume Next\n" +
                "Set fso = CreateObject(\"Scripting.FileSystemObject\")\n" +
                "Set sh = CreateObject(\"WScript.Shell\")\n" +
                "q = Chr(34)\n" +
                "home = fso.GetSpecialFolder(2)\n" +
                "' Auto-copia a la carpeta TEMP para poder borrar la de instalación\n" +
                "If LCase(fso.GetParentFolderName(WScript.ScriptFullName)) <> LCase(home) Then\n" +
                "    fso.CopyFile WScript.ScriptFullName, home & \"\\sigejub-uninstall.vbs\", True\n" +
                "    fso.CopyFile fso.GetParentFolderName(WScript.ScriptFullName) & \"\\uninstall-body.ps1\", home & \"\\sigejub-uninstall-body.ps1\", True\n" +
                "    ifArg = \"\"\n" +
                "    If WScript.Arguments.Named.Exists(\"q\") Then ifArg = \" /q\"\n" +
                "    sh.Run q & home & \"\\sigejub-uninstall.vbs\" & q & ifArg, 0, False\n" +
                "    WScript.Quit 0\n" +
                "End If\n" +
                "body = home & \"\\sigejub-uninstall-body.ps1\"\n" +
                "appFolder = \"@@APPFOLDER@@\"\n" +
                "port = @@SERVERPORT@@\n" +
                "dbPort = @@DBPORT@@\n" +
                "dbPortable = @@DBPORTABLE@@\n" +
                "quiet = WScript.Arguments.Named.Exists(\"q\")\n" +
                "keepDb = 0\n" +
                "If Not quiet Then\n" +
                "    resp = MsgBox(\"SIGEJUB se eliminará del equipo.\" & vbCrLf & vbCrLf & \"Carpeta de instalación:\" & vbCrLf & appFolder & vbCrLf & vbCrLf & \"¿Conservar la base de datos (database\\)?\" & vbCrLf & \"Si = conservar BD y datos    No = borrar todo    Cancelar = salir\", 3 + 32 + 256, \"Desinstalar SIGEJUB\")\n" +
                "    If resp = 2 Then WScript.Quit 3\n" +
                "    If resp = 6 Then keepDb = 1\n" +
                "End If\n" +
                "Set pw = CreateObject(\"WScript.Shell\")\n" +
                "cmd = q & \"powershell.exe\" & q & \" -NoProfile -ExecutionPolicy Bypass -File \" & q & body & q & \" -AppFolder \" & q & appFolder & q & \" -ServerPort \" & port & \" -DbPort \" & dbPort & \" -DbPortable \" & dbPortable & \" -KeepDb \" & keepDb\n" +
                "pw.Run cmd, 0, True\n" +
                "If Not quiet Then\n" +
                "    If keepDb = 1 Then\n" +
                "        MsgBox \"SIGEJUB desinstalado. Se conservó la base de datos:\" & vbCrLf & appFolder & \"\\database\", 64, \"Desinstalar SIGEJUB\"\n" +
                "    Else\n" +
                "        MsgBox \"SIGEJUB desinstalado correctamente.\", 64, \"Desinstalar SIGEJUB\"\n" +
                "    End If\n" +
                "End If\n" +
                "sh.Run \"cmd /c timeout /t 2 /nobreak >nul & del /q \"\"\" & home & \"\\sigejub-uninstall.vbs\"\" \"\"\" & home & \"\\sigejub-uninstall-body.ps1\"\"\", 0, False\n";
        }

        private string UninstallPsBody()
        {
            return
                "# SIGEJUB - lógica de desinstalación (generada por el instalador)\n" +
                "param(\n" +
                "    [string]$AppFolder,\n" +
                "    [int]$ServerPort,\n" +
                "    [int]$DbPort,\n" +
                "    [int]$DbPortable,\n" +
                "    [int]$KeepDb\n" +
                ")\n" +
                "$ErrorActionPreference = 'SilentlyContinue'\n" +
                "\n" +
                "# 1) Detener el servidor web (php -S) y la BD portable por puerto\n" +
                "foreach ($p in @($ServerPort, $(if ($DbPortable -eq 1) { $DbPort }))) {\n" +
                "    if ($p -gt 0) {\n" +
                "        Get-NetTCPConnection -LocalPort $p -State Listen -ErrorAction SilentlyContinue |\n" +
                "            ForEach-Object { Stop-Process -Id $_.OwningProcess -Force -ErrorAction SilentlyContinue }\n" +
                "    }\n" +
                "}\n" +
                "\n" +
                "# 2) Refuerzo: procesos cuyo ejecutable vive dentro de la carpeta (php/mysqld/postgres...)\n" +
                "Get-Process -ErrorAction SilentlyContinue | Where-Object { $_.Path -and $_.Path.StartsWith($AppFolder, [StringComparison]::OrdinalIgnoreCase) } |\n" +
                "    Stop-Process -Force -ErrorAction SilentlyContinue\n" +
                "\n" +
                "Start-Sleep -Milliseconds 300\n" +
                "\n" +
                "# 3) Accesos directos (Escritorio + Menú Inicio)\n" +
                "$prog = [Environment]::GetFolderPath('Programs')\n" +
                "$cprog = [Environment]::GetFolderPath('CommonPrograms')\n" +
                "foreach ($d in @(\n" +
                "    [Environment]::GetFolderPath('Desktop'),\n" +
                "    [Environment]::GetFolderPath('CommonDesktopDirectory'),\n" +
                "    (Join-Path $prog 'SIGEJUB'),\n" +
                "    (Join-Path $cprog 'SIGEJUB')\n" +
                ")) {\n" +
                "    $lnk = Join-Path $d 'SIGEJUB.lnk'\n" +
                "    if (Test-Path -LiteralPath $lnk) { Remove-Item -LiteralPath $lnk -Force -ErrorAction SilentlyContinue }\n" +
                "    $lnk2 = Join-Path $d 'Desinstalar SIGEJUB.lnk'\n" +
                "    if (Test-Path -LiteralPath $lnk2) { Remove-Item -LiteralPath $lnk2 -Force -ErrorAction SilentlyContinue }\n" +
                "    if ((Test-Path -LiteralPath $d) -and ((Get-ChildItem -LiteralPath $d -Force -ErrorAction SilentlyContinue | Measure-Object).Count -eq 0)) {\n" +
                "        Remove-Item -LiteralPath $d -Force -ErrorAction SilentlyContinue\n" +
                "    }\n" +
                "}\n" +
                "\n" +
                "# 4) Entrada de registro \"Agregar o quitar programas\"\n" +
                "reg delete \"HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\Uninstall\\SIGEJUB\" /f 2>$null | Out-Null\n" +
                "reg delete \"HKLM\\Software\\Microsoft\\Windows\\CurrentVersion\\Uninstall\\SIGEJUB\" /f 2>$null | Out-Null\n" +
                "\n" +
                "# 5) Borrar la carpeta de instalación (o conservar database\\ si se pidió)\n" +
                "if (-not (Test-Path -LiteralPath $AppFolder)) { exit 0 }\n" +
                "if ($KeepDb -eq 1) {\n" +
                "    $bd = Join-Path $AppFolder 'database'\n" +
                "    Get-ChildItem -LiteralPath $AppFolder -Force -ErrorAction SilentlyContinue |\n" +
                "        Where-Object { $_.FullName -ne $bd } |\n" +
                "        ForEach-Object {\n" +
                "            if ($_.PSIsContainer) { Remove-Item -LiteralPath $_.FullName -Recurse -Force -ErrorAction SilentlyContinue }\n" +
                "            else { Remove-Item -LiteralPath $_.FullName -Force -ErrorAction SilentlyContinue }\n" +
                "        }\n" +
                "} else {\n" +
                "    Remove-Item -LiteralPath $AppFolder -Recurse -Force -ErrorAction SilentlyContinue\n" +
                "}\n" +
                "# Reintento si algún proceso tardó en soltar los archivos\n" +
                "if (($KeepDb -eq 0) -and (Test-Path -LiteralPath $AppFolder)) {\n" +
                "    Start-Sleep -Seconds 2\n" +
                "    Remove-Item -LiteralPath $AppFolder -Recurse -Force -ErrorAction SilentlyContinue\n" +
                "}\n" +
                "exit 0\n";
        }

        // Registra la entrada de "Agregar o quitar programas" para el usuario actual.
        private void RegisterUninstall(string appPath)
        {
            try
            {
                string vbs = Path.Combine(appPath, "uninstall.vbs");
                string icon = Path.Combine(appPath, "public", "img", "imagen_2026-05-19_065531142.ico");
                // HKCU (siempre) y HKLM (si hay permisos de administrador)
                RegistryKey[] hives = null;
                try { hives = new RegistryKey[] { Registry.CurrentUser, Registry.LocalMachine }; }
                catch { hives = new RegistryKey[] { Registry.CurrentUser }; }
                foreach (var hive in hives)
                {
                    try
                    {
                        using (var key = hive.CreateSubKey(@"Software\Microsoft\Windows\CurrentVersion\Uninstall\SIGEJUB"))
                        {
                            if (key == null) continue;
                            key.SetValue("DisplayName", "SIGEJUB - Sistema de Gestión de Jubilaciones");
                            key.SetValue("DisplayVersion", "1.0.0");
                            key.SetValue("Publisher", "Equipo SIGEJUB");
                            key.SetValue("InstallLocation", appPath);
                            key.SetValue("DisplayIcon", icon);
                            key.SetValue("UninstallString", "\"wscript.exe\" \"" + vbs + "\"");
                            key.SetValue("QuietUninstallString", "\"wscript.exe\" \"" + vbs + "\" /q");
                            key.SetValue("NoModify", 1, RegistryValueKind.DWord);
                            key.SetValue("NoRepair", 1, RegistryValueKind.DWord);
                            key.SetValue("EstimatedSize", 0, RegistryValueKind.DWord);
                        }
                    }
                    catch { } // sin permisos para HKLM -> se ignora
                }
            }
            catch (Exception ex) { AppendLog("[AVISO] Registro desinstalación: " + ex.Message, Color.Orange); }
        }

        // ─── Helpers de proceso ───
        private bool RunCmd(string cmd, string args, out string output) { return RunCmdIn(cmd, args, SourceRoot, out output); }

        // Ejecuta un proceso sin ventana de consola y espera a que termine.
        private void RunHidden(string cmd, string args)
        {
            try
            {
                using (var proc = Process.Start(new ProcessStartInfo(cmd, args)
                {
                    UseShellExecute = false,
                    CreateNoWindow = true,
                    WindowStyle = ProcessWindowStyle.Hidden
                }))
                {
                    if (proc != null) { if (!proc.WaitForExit(120000)) { try { proc.Kill(); } catch { } } }
                }
            }
            catch { }
        }

        private bool RunCmdIn(string cmd, string args, string workDir, out string output, Dictionary<string, string> env = null, bool detach = false)
        {
            output = "";
            try
            {
                var psi = new ProcessStartInfo(cmd, args)
                {
                    RedirectStandardOutput = !detach,
                    RedirectStandardError  = !detach,
                    UseShellExecute        = false,
                    CreateNoWindow         = true,
                    WindowStyle            = ProcessWindowStyle.Hidden,
                    WorkingDirectory       = workDir,
                };
                if (!detach)
                {
                    psi.StandardOutputEncoding = Encoding.UTF8;
                    psi.StandardErrorEncoding  = Encoding.UTF8;
                }
                if (env != null)
                {
                    foreach (var kv in env)
                    {
                        try { psi.EnvironmentVariables[kv.Key] = kv.Value; } catch { }
                    }
                }
                using (var proc = Process.Start(psi))
                {
                    // Modo detached: el proceso lanza un demonio en segundo plano (p.ej. pg_ctl start
                    // lanza postgres.exe) y termina. No se redirige la salida y se espera hasta 10 s.
                    if (detach)
                    {
                        proc.WaitForExit(10000);
                        output = "(detached)";
                        return !proc.HasExited || proc.ExitCode == 0;
                    }

                    // Leer stderr de forma asíncrona evita el deadlock cuando el
                    // proceso genera mucha salida (p.ej. composer install).
                    StringBuilder err = new StringBuilder();
                    proc.ErrorDataReceived += (s, e2) => { if (e2.Data != null) { lock (err) err.AppendLine(e2.Data); } };
                    proc.BeginErrorReadLine();
                    StringBuilder sbOut = new StringBuilder();
                    proc.OutputDataReceived += (s, e2) => { if (e2.Data != null) { lock (sbOut) sbOut.AppendLine(e2.Data); } };
                    proc.BeginOutputReadLine();
                    if (!proc.WaitForExit(600000))
                    {
                        try { proc.Kill(); } catch { }
                        output = "Tiempo de espera agotado (más de 10 minutos).";
                        return false;
                    }
                    output = (sbOut.ToString() + "\n" + err.ToString()).Trim();
                    return proc.ExitCode == 0;
                }
            }
            catch (Exception ex) { output = ex.Message; return false; }
        }

        private string ReplaceEnv(string content, string key, string value)
        {
            var lines = content.Split('\n');
            for (int i = 0; i < lines.Length; i++)
                if (lines[i].StartsWith(key + "=", StringComparison.OrdinalIgnoreCase))
                    lines[i] = key + "=" + value;
            return string.Join("\n", lines);
        }

        private string RemoveEnvLine(string content, string key)
        {
            var lines = content.Split('\n');
            var result = new List<string>();
            foreach (var line in lines)
                if (!line.StartsWith(key + "=", StringComparison.OrdinalIgnoreCase))
                    result.Add(line);
            return string.Join("\n", result);
        }

        private int FindFreePort()
        {
            for (int port = 8000; port <= 9000; port++)
            {
                try { var l = new System.Net.Sockets.TcpListener(System.Net.IPAddress.Loopback, port); l.Start(); l.Stop(); return port; }
                catch { }
            }
            return 8000;
        }

        private string Truncate(string s, int max) { return s.Length <= max ? s : s.Substring(0, max) + "..."; }

        // ─── Helpers de quoting VBS ───
        // Escapa un texto para que sea seguro dentro de un literal de VBScript
        // (las comillas se duplican: " -> "")
        private static string VbsInner(string s) { return (s ?? "").Replace("\"", "\"\""); }
        // Devuelve un literal de VBScript (texto entre comillas dobles) listo para embeber en un archivo .vbs
        private static string VbsStr(string s) { return "\"" + VbsInner(s) + "\""; }

        private void AppendLog(string text, Color? color = null)
        {
            if (logBox == null || logBox.IsDisposed) return;
            if (logBox.InvokeRequired) { logBox.Invoke(new Action(() => AppendLog(text, color))); return; }
            logBox.SelectionStart = logBox.TextLength;
            logBox.SelectionLength = 0;
            logBox.SelectionColor = color ?? Color.White;
            logBox.AppendText(text + "\n");
            logBox.ScrollToCaret();
        }

        private void SetStatus(string text)
        {
            if (lblStatus == null || lblStatus.IsDisposed) return;
            if (lblStatus.InvokeRequired) { lblStatus.Invoke(new Action(() => SetStatus(text))); return; }
            lblStatus.Text = text;
        }

        private void SetProgress(int value)
        {
            if (progressBar == null || progressBar.IsDisposed) return;
            if (progressBar.InvokeRequired) { progressBar.Invoke(new Action(() => SetProgress(value))); return; }
            progressBar.Value = Math.Min(value, 100);
        }

        private Icon LoadIcon()
        {
            var path = Path.Combine(SourceRoot, "public", "img", "imagen_2026-05-19_065531142.ico");
            if (File.Exists(path)) try { return new Icon(path); } catch { }
            return SystemIcons.Application;
        }

        protected override void OnFormClosing(FormClosingEventArgs e)
        {
            if (installing)
            {
                e.Cancel = true;
                MessageBox.Show("La instalación está en curso. Espera a que termine.", "SIGEJUB",
                    MessageBoxButtons.OK, MessageBoxIcon.Information);
            }
            base.OnFormClosing(e);
        }
    }
}
