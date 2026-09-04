using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Drawing;
using System.Drawing.Drawing2D;
using System.IO;
using System.Runtime.InteropServices;
using System.Text;
using System.Threading;
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

    // ── Paleta de colores reutilizable ─────────────
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

    // ── Botón moderno (redondeado, con hover) ──────
    public class RoundBtn : Button
    {
        public bool IsPrimary { get; set; }
        public RoundBtn()
        {
            FlatStyle = FlatStyle.Flat;
            FlatAppearance.BorderSize = 0;
            BackColor = P.Indigo;
            ForeColor = Color.White;
            Font = new Font("Segoe UI", 10, FontStyle.Bold);
            Cursor = Cursors.Hand;
            Height = 40;
            UseVisualStyleBackColor = false;
        }
        protected override void OnPaint(PaintEventArgs e)
        {
            var g = e.Graphics;
            g.SmoothingMode = SmoothingMode.AntiAlias;
            var rc = new Rectangle(0, 0, Width - 1, Height - 1);
            using (var path = Rounded(rc, 10))
            {
                var bg = Parent == null ? BackColor : BackColor;
                if (IsPrimary)
                {
                    bg = P.Indigo;
                    if (Focused) bg = P.IndigoDark;
                    else if (ClientRectangle.Contains(PointToClient(Cursor.Position))) bg = Color.FromArgb(105, 108, 245);
                }
                using (var b = new SolidBrush(bg)) g.FillPath(b, path);
            }
            TextRenderer.DrawText(g, Text, Font, rc, ForeColor,
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

    // ── Panel de pasos (sidebar) ─────────────────────
    public class StepsPanel : Panel
    {
        public List<string> Items = new List<string>();
        public int Current = 0;
        private Color[] tints = { Color.FromArgb(129, 140, 248), Color.FromArgb(129, 140, 248), Color.FromArgb(129, 140, 248), Color.FromArgb(129, 140, 248) };
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
                    using (var b = new SolidBrush(active ? P.Indigo : (done ? Color.FromArgb(30, 41, 59) : Color.FromArgb(30, 41, 59))))
                        g.FillEllipse(b, circle);
                    using (var pen = new Pen(done || active ? P.Indigo : Color.FromArgb(71, 85, 105), 1.5f))
                        g.DrawEllipse(pen, circle);
                    string num = done ? "✓" : (i + 1).ToString();
                    using (var b = new SolidBrush(active || done ? Color.White : Color.FromArgb(148, 163, 184)))
                        g.DrawString(num, new Font("Segoe UI", 10, FontStyle.Bold), b, circle.X + 8, circle.Y + 5);
                    using (var b = new SolidBrush(active ? Color.White : Color.FromArgb(148, 163, 184)))
                        g.DrawString(Items[i], new Font("Segoe UI", 10, FontStyle.Bold), b, 78, cy + 4);
                }
            }
        }
        public void RefreshSteps() { Invalidate(); }
    }

    // ── Instalador principal ─────────────────────────
    public class InstallerForm : Form
    {
        private StepsPanel steps;
        private Panel content;
        private Panel currentPage;
        private RoundBtn btnBack, btnNext;
        private Label lblTitle;

        private int step = 0;
        private const int STEP_WELCOME = 0;
        private const int STEP_FOLDER  = 1;
        private const int STEP_DB      = 2;
        private const int STEP_INSTALL = 3;
        private const int STEP_DONE    = 4;
        private const int STEP_COUNT   = 4;

        // Config
        private string installPath = Environment.GetFolderPath(Environment.SpecialFolder.ProgramFilesX86) + "\\SIGEJUB";
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
        private RadioButton rdoMysql, rdoPgsql;
        private TextBox txtHost, txtPort, txtDb, txtUser, txtPass;
        private RichTextBox logBox;
        private ProgressBar progressBar;
        private Label lblStatus, lblDoneUrl;
        private bool installing = false;

        private string SourceRoot = null;

        public InstallerForm()
        {
            // Detectar la raíz de la app (carpeta del exe)
            SourceRoot = Application.StartupPath;
            if (File.Exists(Path.Combine(SourceRoot, "artisan"))) { }
            else if (File.Exists(Path.Combine(SourceRoot, "app", "artisan"))) SourceRoot = Path.Combine(SourceRoot, "app");
            else if (File.Exists(Path.Combine(SourceRoot, "..", "artisan"))) SourceRoot = Path.GetFullPath(Path.Combine(SourceRoot, ".."));

            this.Text = "SIGEJUB - Instalador";
            this.Size = new Size(940, 600);
            this.MinimumSize = new Size(860, 560);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.FormBorderStyle = FormBorderStyle.FixedSingle;
            this.MaximizeBox = false;
            this.Icon = LoadIcon();
            this.BackColor = P.Bg;

            steps = new StepsPanel { Dock = DockStyle.Left, Width = 300 };
            steps.Items = new List<string> { "Bienvenida", "Carpeta de instalación", "Base de datos", "Instalación" };
            steps.Current = 0;

            content = new Panel { Dock = DockStyle.Fill, BackColor = P.Bg, Padding = new Padding(8) };

            this.Controls.Add(steps);
            this.Controls.Add(content);

            // Botones inferiores
            btnBack = new RoundBtn { Text = "Atrás", IsPrimary = false, Width = 110, AutoSize = false };
            btnBack.BackColor = P.Bg; btnBack.ForeColor = P.Muted; btnBack.Font = new Font("Segoe UI", 10);
            btnNext = new RoundBtn { Text = "Siguiente", IsPrimary = true, Width = 140, AutoSize = false };
            btnBack.Click += (s, e) => Go(step - 1);
            btnNext.Click += (s, e) => Next();
            content.Controls.Add(btnBack);
            content.Controls.Add(btnNext);

            ShowPage(STEP_WELCOME);
        }

        private void Next()
        {
            if (step == STEP_FOLDER && !ValidateFolder()) return;
            if (step == STEP_DB) { ReadDb(); }
            if (step == STEP_INSTALL)
            {
                if (installing) return;
                btnNext.Text = "Instalando...";
                btnNext.Enabled = false;
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
            var p = new Panel { Location = new Point(0, 0), Size = new Size(content.Width - 40, 84), BackColor = P.Bg };
            lblTitle = new Label
            {
                Text = title, AutoSize = false,
                Font = new Font("Segoe UI", 17, FontStyle.Bold),
                ForeColor = P.Navy, Location = new Point(18, 16), Size = new Size(560, 32)
            };
            var sub = new Label
            {
                Text = subtitle, AutoSize = false,
                Font = new Font("Segoe UI", 10),
                ForeColor = P.Muted, Location = new Point(18, 50), Size = new Size(560, 20)
            };
            p.Controls.Add(lblTitle);
            p.Controls.Add(sub);
            return p;
        }

        private void ClearContent()
        {
            foreach (Control c in content.Controls)
            {
                if (c == btnBack || c == btnNext) continue;
                c.Dispose();
            }
            content.Controls.Clear();
        }

        private void ShowPage(int s)
        {
            ClearContent();
            currentPage = new Panel { Location = new Point(0, 0), Size = new Size(content.Width, content.Height - 70), BackColor = P.Bg };
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

            btnBack.Visible = (s != STEP_WELCOME && s != STEP_DONE);
            btnNext.Visible = (s != STEP_DONE);
            if (s == STEP_WELCOME) { btnNext.Text = "Siguiente"; }
            else if (s == STEP_FOLDER) { btnNext.Text = "Siguiente"; }
            else if (s == STEP_DB) { btnNext.Text = "Instalar"; }
            else if (s == STEP_INSTALL) { btnNext.Text = "Instalando..."; btnNext.Enabled = false; }
            btnBack.Enabled = !(s == STEP_INSTALL || s == STEP_DONE);

            // Reposicionar botones
            btnNext.Location = new Point(content.Width - 160, content.Height - 58);
            btnBack.Location = new Point(content.Width - 290, content.Height - 58);
            btnNext.BringToFront();
            btnBack.BringToFront();

            if (s == STEP_INSTALL) StartInstall();
        }

        // ── PÁGINA 0: BIENVENIDA ─────────────────────
        private void BuildWelcome()
        {
            var h = MakeHeader("¡Bienvenido a SIGEJUB!", "Sistema Integral de Gestión de Jubilaciones");
            currentPage.Controls.Add(h);

            var card = new Panel
            {
                BackColor = P.Card, Location = new Point(18, 96),
                Size = new Size(content.Width - 36, content.Height - 176),
                Padding = new Padding(24)
            };
            card.Paint += (s, e) =>
            {
                var g = e.Graphics; g.SmoothingMode = SmoothingMode.AntiAlias;
                using (var p = new Pen(P.Border, 1)) { g.DrawRectangle(p, 0, 0, card.Width - 1, card.Height - 1); }
            };

            var inner = new Panel { BackColor = P.Card, Location = new Point(24, 24), Size = new Size(card.Width - 48, card.Height - 48) };

            var lbl1 = new Label
            {
                Text = "Este asistente instalará SIGEJUB en tu equipo.", AutoSize = false,
                Location = new Point(0, 0), Size = new Size(inner.Width, 28),
                Font = new Font("Segoe UI", 11), ForeColor = P.Text
            };
            var lbl2 = new Label
            {
                Text = "Durante la instalación:", AutoSize = false,
                Location = new Point(0, 36), Size = new Size(inner.Width, 24),
                Font = new Font("Segoe UI", 10, FontStyle.Bold), ForeColor = P.Navy
            };
            inner.Controls.Add(lbl1); inner.Controls.Add(lbl2);

            string[] puntos = {
                "• Se copiarán los archivos de la aplicación a la carpeta de tu elección.",
                "• Se verificará PHP 8.2+ y Composer en tu sistema.",
                "• Se configurará la base de datos (MySQL / MariaDB o PostgreSQL).",
                "• Se instalará un acceso directo en el escritorio.",
                "• Se generará una URL local para comenzar a usar el sistema."
            };
            int y = 66;
            foreach (var pt in puntos)
            {
                var l = new Label { Text = pt, AutoSize = false, Location = new Point(0, y), Size = new Size(inner.Width, 24), Font = new Font("Segoe UI", 10), ForeColor = P.Muted };
                inner.Controls.Add(l); y += 28;
            }

            var req = new Label
            {
                Text = "Requisitos: PHP 8.2+ y Composer instalados. La base de datos debe existir en el servidor.",
                AutoSize = false, Location = new Point(0, y + 12), Size = new Size(inner.Width, 40),
                Font = new Font("Segoe UI", 9, FontStyle.Italic), ForeColor = Color.FromArgb(180, 83, 9)
            };
            inner.Controls.Add(req);

            card.Controls.Add(inner);
            currentPage.Controls.Add(card);
        }

        // ── PÁGINA 1: CARPETA ────────────────────────
        private void BuildFolder()
        {
            var h = MakeHeader("Elige dónde instalar", "Selecciona la carpeta donde se copiará la aplicación.");
            currentPage.Controls.Add(h);

            var card = new Panel { BackColor = P.Card, Location = new Point(18, 96), Size = new Size(content.Width - 36, content.Height - 176) };
            card.Paint += (s, e) => { using (var p = new Pen(P.Border, 1)) e.Graphics.DrawRectangle(p, 0, 0, card.Width - 1, card.Height - 1); };

            var lbl = new Label
            {
                Text = "Carpeta de instalación:", Location = new Point(24, 24), Size = new Size(200, 22),
                Font = new Font("Segoe UI", 10, FontStyle.Bold), ForeColor = P.Navy
            };
            txtPath = new TextBox
            {
                Text = installPath, Location = new Point(24, 52), Size = new Size(card.Width - 150, 30),
                Font = new Font("Segoe UI", 10), BorderStyle = BorderStyle.FixedSingle
            };
            txtPath.TextChanged += (s, e) => { installPath = txtPath.Text.Trim(); };
            var btnBrowse = new RoundBtn { Text = "Examinar...", IsPrimary = false, Width = 96, Height = 32, Location = new Point(card.Width - 122, 50), Font = new Font("Segoe UI", 9) };
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
                Location = new Point(24, 92), Size = new Size(400, 20), Font = new Font("Segoe UI", 9), ForeColor = P.Muted
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

        // ── PÁGINA 2: BD ─────────────────────────────
        private void BuildDb()
        {
            var h = MakeHeader("Configuración de la base de datos", "Indica el gestor y las credenciales de tu servidor de BD.");
            currentPage.Controls.Add(h);

            var card = new Panel { BackColor = P.Card, Location = new Point(18, 96), Size = new Size(content.Width - 36, content.Height - 176) };
            card.Paint += (s, e) => { using (var p = new Pen(P.Border, 1)) e.Graphics.DrawRectangle(p, 0, 0, card.Width - 1, card.Height - 1); };

            var lblEngine = new Label { Text = "Gestor de base de datos:", Location = new Point(24, 24), Size = new Size(220, 22), Font = new Font("Segoe UI", 10, FontStyle.Bold), ForeColor = P.Navy };
            rdoMysql = new RadioButton { Text = "MySQL / MariaDB", Location = new Point(24, 52), Size = new Size(150, 24), Checked = dbEngine == "mysql", Font = new Font("Segoe UI", 10) };
            rdoPgsql = new RadioButton { Text = "PostgreSQL", Location = new Point(180, 52), Size = new Size(150, 24), Checked = dbEngine == "pgsql", Font = new Font("Segoe UI", 10) };
            rdoMysql.CheckedChanged += (s, e) => { if (rdoMysql.Checked) { dbEngine = "mysql"; txtPort.Text = "3306"; txtUser.Text = "root"; } };
            rdoPgsql.CheckedChanged += (s, e) => { if (rdoPgsql.Checked) { dbEngine = "pgsql"; txtPort.Text = "5432"; txtUser.Text = "postgres"; } };

            int y = 96;
            var lblHost = new Label { Text = "Host:", Location = new Point(24, y + 4), Size = new Size(120, 22), ForeColor = P.Muted };
            txtHost = new TextBox { Text = dbHost, Location = new Point(150, y), Size = new Size(card.Width - 190, 26), BorderStyle = BorderStyle.FixedSingle };
            y += 34;
            var lblPort = new Label { Text = "Puerto:", Location = new Point(24, y + 4), Size = new Size(120, 22), ForeColor = P.Muted };
            txtPort = new TextBox { Text = dbPort, Location = new Point(150, y), Size = new Size(120, 26), BorderStyle = BorderStyle.FixedSingle };
            y += 34;
            var lblDb = new Label { Text = "Nombre de la BD:", Location = new Point(24, y + 4), Size = new Size(120, 22), ForeColor = P.Muted };
            txtDb = new TextBox { Text = dbName, Location = new Point(150, y), Size = new Size(card.Width - 190, 26), BorderStyle = BorderStyle.FixedSingle };
            y += 34;
            var lblUser = new Label { Text = "Usuario:", Location = new Point(24, y + 4), Size = new Size(120, 22), ForeColor = P.Muted };
            txtUser = new TextBox { Text = dbUser, Location = new Point(150, y), Size = new Size(card.Width - 190, 26), BorderStyle = BorderStyle.FixedSingle };
            y += 34;
            var lblPass = new Label { Text = "Clave:", Location = new Point(24, y + 4), Size = new Size(120, 22), ForeColor = P.Muted };
            txtPass = new TextBox { Text = dbPass, Location = new Point(150, y), Size = new Size(card.Width - 190, 26), BorderStyle = BorderStyle.FixedSingle, PasswordChar = '*' };
            y += 30;

            var nota = new Label
            {
                Text = "La base de datos debe existir en el servidor. Dejar en blanco usa los valores por defecto.",
                AutoSize = false, Location = new Point(24, y + 6), Size = new Size(card.Width - 60, 40),
                Font = new Font("Segoe UI", 9, FontStyle.Italic), ForeColor = Color.FromArgb(180, 83, 9)
            };

            card.Controls.AddRange(new Control[] { lblEngine, rdoMysql, rdoPgsql, lblHost, txtHost, lblPort, txtPort, lblDb, txtDb, lblUser, txtUser, lblPass, txtPass, nota });
            currentPage.Controls.Add(card);
        }

        private void ReadDb()
        {
            dbHost = string.IsNullOrWhiteSpace(txtHost.Text) ? "127.0.0.1" : txtHost.Text.Trim();
            dbPort = string.IsNullOrWhiteSpace(txtPort.Text) ? (dbEngine == "pgsql" ? "5432" : "3306") : txtPort.Text.Trim();
            dbName = string.IsNullOrWhiteSpace(txtDb.Text) ? "bd-sigejub" : txtDb.Text.Trim();
            dbUser = string.IsNullOrWhiteSpace(txtUser.Text) ? (dbEngine == "pgsql" ? "postgres" : "root") : txtUser.Text.Trim();
            dbPass = txtPass.Text;
        }

        // ── PÁGINA 3: INSTALACIÓN / PROGRESO ─────────
        private void BuildInstall()
        {
            var h = MakeHeader("Instalando SIGEJUB...", "Esto puede tardar unos minutos. No cierres la ventana.");
            currentPage.Controls.Add(h);

            logBox = new RichTextBox
            {
                Location = new Point(18, 96), Size = new Size(content.Width - 36, content.Height - 210),
                ReadOnly = true, BackColor = P.Navy, ForeColor = Color.FromArgb(226, 232, 240),
                Font = new Font("Consolas", 9.5f), BorderStyle = BorderStyle.FixedSingle
            };
            progressBar = new ProgressBar
            {
                Location = new Point(18, content.Height - 108), Size = new Size(content.Width - 36, 24),
                Minimum = 0, Maximum = 100, Value = 0, Style = ProgressBarStyle.Continuous
            };
            lblStatus = new Label
            {
                Text = "Preparando...", Location = new Point(18, content.Height - 78),
                Size = new Size(content.Width - 36, 22), ForeColor = P.Muted, Font = new Font("Segoe UI", 9)
            };
            currentPage.Controls.Add(logBox);
            currentPage.Controls.Add(progressBar);
            currentPage.Controls.Add(lblStatus);
            logBox.AppendText("  Preparando instalación en: " + installPath + "\n\n");
        }

        // ── PÁGINA 4: FINALIZAR ──────────────────────
        private void BuildDone()
        {
            var h = MakeHeader("Instalación completada", "SIGEJUB ha sido instalado correctamente.");
            currentPage.Controls.Add(h);

            var card = new Panel { BackColor = P.Card, Location = new Point(18, 96), Size = new Size(content.Width - 36, content.Height - 176) };
            card.Paint += (s, e) => { using (var p = new Pen(P.Border, 1)) e.Graphics.DrawRectangle(p, 0, 0, card.Width - 1, card.Height - 1); };

            var check = new Label
            {
                Text = "✓", Font = new Font("Segoe UI", 44, FontStyle.Bold),
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

            btnBack.Visible = false;

            // botón Iniciar
            var btnStart = new RoundBtn { Text = "Iniciar SIGEJUB", IsPrimary = true, Width = 180, Height = 44 };
            btnStart.Location = new Point(content.Width - 200, content.Height - 60);
            btnStart.Click += (s, e) => {
                string vbs = Path.Combine(installPath, "sigejub-start.vbs");
                if (File.Exists(vbs)) Process.Start(vbs);
                else { Process.Start("http://localhost:" + selectedPort); Process.Start(new ProcessStartInfo { FileName = "php", Arguments = "artisan serve --port=" + selectedPort, WorkingDirectory = installPath, UseShellExecute = true }); }
                this.Close();
            };
            content.Controls.Add(btnStart);
            btnStart.BringToFront();

            btnNext.Visible = false;
        }

        private void StartInstall()
        {
            if (installing) return;
            installing = true;
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

                // ── 1: Copiar archivos de la app ───
                AppendLog("[1/8] Copiando archivos de la aplicación...", Color.Yellow);
                CopyDirectory(SourceRoot, installPath);
                SetProgress(12);
                AppendLog("[OK] Archivos copiados a " + installPath, Color.Green);

                // ── 2: PHP ─────────────────────────
                AppendLog("[2/8] Verificando PHP...", Color.Yellow);
                string output;
                if (!RunCmd("where", "php", out output))
                {
                    AppendLog("[ERROR] PHP no encontrado en PATH. Instala PHP 8.2+ y agrégalo al PATH.", Color.Red);
                    FinishWithError(); return;
                }
                string phpVer = "";
                RunCmd("php", "-r \"echo PHP_MAJOR_VERSION;\"", out phpVer);
                AppendLog("[OK] PHP " + phpVer + " encontrado", Color.Green);
                SetProgress(20);

                // ── 3: Composer ────────────────────
                AppendLog("[3/8] Verificando Composer...", Color.Yellow);
                if (!RunCmd("where", "composer", out output))
                {
                    AppendLog("[ERROR] Composer no encontrado. Instala Composer.", Color.Red);
                    FinishWithError(); return;
                }
                AppendLog("[OK] Composer encontrado", Color.Green);
                SetProgress(28);

                // ── 4: .env ────────────────────────
                AppendLog("[4/8] Configurando .env...", Color.Yellow);
                string envPath = Path.Combine(installPath, ".env");
                string envExample = Path.Combine(installPath, ".env.example");
                if (!File.Exists(envPath))
                {
                    if (File.Exists(envExample)) File.Copy(envExample, envPath);
                    else { AppendLog("[ERROR] Falta .env.example", Color.Red); FinishWithError(); return; }
                }
                string env = File.ReadAllText(envPath, Encoding.UTF8);
                env = RemoveEnvLine(env, "DB_HOST_PGSQL"); env = RemoveEnvLine(env, "DB_PORT_PGSQL");
                env = RemoveEnvLine(env, "DB_DATABASE_PGSQL"); env = RemoveEnvLine(env, "DB_USERNAME_PGSQL");
                env = RemoveEnvLine(env, "DB_PASSWORD_PGSQL");
                env = ReplaceEnv(env, "DB_CONNECTION", dbEngine);
                env = ReplaceEnv(env, "DB_HOST", dbHost);
                env = ReplaceEnv(env, "DB_PORT", dbPort);
                env = ReplaceEnv(env, "DB_DATABASE", dbName);
                env = ReplaceEnv(env, "DB_USERNAME", dbUser);
                env = ReplaceEnv(env, "DB_PASSWORD", dbPass);
                File.WriteAllText(envPath, env, Encoding.UTF8);
                AppendLog("[OK] BD configurada en .env (" + (dbEngine == "pgsql" ? "PostgreSQL" : "MySQL/MariaDB") + ")", Color.Green);
                SetProgress(38);

                // ── 5: composer install ───────────
                AppendLog("[5/8] Instalando dependencias (esto puede tardar)...", Color.Yellow);
                if (!RunCmdIn("composer", "install --no-interaction --no-ansi --no-progress", installPath, out output))
                    AppendLog("[ADVERTENCIA] composer: " + Truncate(output, 200), Color.Orange);
                else
                    AppendLog("[OK] Dependencias instaladas", Color.Green);
                SetProgress(52);

                // ── 6: key ────────────────────────
                AppendLog("[6/8] Generando APP_KEY...", Color.Yellow);
                if (!RunCmdIn("php", "artisan key:generate --force --no-ansi", installPath, out output))
                    AppendLog("[ADVERTENCIA] key: " + Truncate(output, 200), Color.Orange);
                else AppendLog("[OK] APP_KEY generada", Color.Green);
                SetProgress(64);

                // ── 7: migrar ─────────────────────
                AppendLog("[7/8] Ejecutando migraciones...", Color.Yellow);
                if (!RunCmdIn("php", "artisan migrate --force --no-ansi", installPath, out output))
                    AppendLog("[ADVERTENCIA] Migraciones: " + Truncate(output, 250), Color.Orange);
                else AppendLog("[OK] Migraciones ejecutadas", Color.Green);
                SetProgress(78);

                // ── 8: finalizar ──────────────────
                AppendLog("[8/8] Finalizando...", Color.Yellow);
                RunCmdIn("php", "artisan optimize:clear --no-ansi", installPath, out output);
                string pubStorage = Path.Combine(installPath, "public", "storage");
                if (Directory.Exists(pubStorage)) Directory.Delete(pubStorage, true);
                RunCmdIn("php", "artisan storage:link --no-ansi", installPath, out output);
                SetProgress(88);

                selectedPort = FindFreePort();
                env = File.ReadAllText(envPath, Encoding.UTF8);
                env = ReplaceEnv(env, "APP_URL", "http://localhost:" + selectedPort);
                File.WriteAllText(envPath, env, Encoding.UTF8);
                SetProgress(94);

                // Acceso directo
                if (createShortcut) CreateShortcut(installPath);
                SetProgress(100);

                // SVB de arranque
                WriteLaunchVbs(installPath);

                AppendLog("", Color.White);
                AppendLog("  ✓ INSTALACIÓN COMPLETADA", Color.Cyan);
                AppendLog("  Puerto: " + selectedPort, Color.Cyan);
                AppendLog("  URL:    http://localhost:" + selectedPort, Color.Cyan);

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

        private void FinishWithError()
        {
            AppendLog("[ERROR] Instalación cancelada.", Color.Red);
            SetStatus("Error durante la instalación");
            this.Invoke(new Action(() =>
            {
                installing = false;
                btnNext.Enabled = true;
                btnNext.Text = "Reintentar";
            }));
        }

        // ── Copia de directorio ─────────────────────
        private static readonly HashSet<string> ExcludeTop = new HashSet<string>(StringComparer.OrdinalIgnoreCase)
        {
            ".git", "vendor", "node_modules", "installer-src",
            "SIGEJUB-Installer.exe", "build-installer.ps1", "setup.bat", "setup.sh",
            "inicio.php", "detener.bat", "detener.sh", "start.bat", "start.sh",
            "sigejub-start.vbs", "README.md", "storage", "bd-sigejub"
        };

        private void CopyDirectory(string src, string dst)
        {
            Directory.CreateDirectory(dst);
            foreach (var dir in Directory.GetDirectories(src, "*", SearchOption.TopDirectoryOnly))
            {
                string name = Path.GetFileName(dir);
                if (ExcludeTop.Contains(name)) continue;
                CopyDirectory(dir, Path.Combine(dst, name));
            }
            foreach (var file in Directory.GetFiles(src))
            {
                string name = Path.GetFileName(file);
                if (name.StartsWith(".env", StringComparison.OrdinalIgnoreCase)) continue;
                File.Copy(file, Path.Combine(dst, name), true);
            }
        }

        private void CreateShortcut(string appPath)
        {
            try
            {
                string desktop = Environment.GetFolderPath(Environment.SpecialFolder.Desktop);
                string vbsPath = Path.Combine(appPath, "sigejub-start.vbs");
                string lnkPath = Path.Combine(desktop, "SIGEJUB.lnk");
                AppendLog("Creando acceso directo en: " + desktop, Color.Gray);

                string ico = Path.Combine(appPath, "public", "img", "imagen_2026-05-19_065531142.ico") + ", 0";
                string vbs = Path.GetTempFileName() + ".vbs";
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
                if (File.Exists(lnkPath)) AppendLog("[OK] Acceso directo creado", Color.Green);
                else AppendLog("[AVISO] Acceso directo podría no haberse creado", Color.Orange);
            }
            catch (Exception ex) { AppendLog("[AVISO] Acceso directo: " + ex.Message, Color.Orange); }
        }

        private void WriteLaunchVbs(string appPath)
        {
            try
            {
                string vbs = Path.Combine(appPath, "sigejub-start.vbs");
                File.WriteAllText(vbs,
                    "On Error Resume Next\n" +
                    "Set sh = CreateObject(\"WScript.Shell\")\n" +
                    "ret = sh.Run(\"cmd /k cd /d \"\"\" & \"" + appPath + "\" & \"\"\" \" & \" && php artisan serve --port=" + selectedPort + "\", 1, False)\n",
                    Encoding.UTF8);
            }
            catch { }
        }

        // ── Helpers de proceso ──────────────────────
        private bool RunCmd(string cmd, string args, out string output) { return RunCmdIn(cmd, args, SourceRoot, out output); }

        private bool RunCmdIn(string cmd, string args, string workDir, out string output)
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
                    WorkingDirectory = workDir,
                    StandardOutputEncoding = Encoding.UTF8,
                    StandardErrorEncoding = Encoding.UTF8
                };
                using (var proc = Process.Start(psi))
                {
                    string o = proc.StandardOutput.ReadToEnd();
                    string e = proc.StandardError.ReadToEnd();
                    proc.WaitForExit(600000);
                    output = (o + e).Trim();
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
            base.OnFormClosing(e);
        }
    }
}
