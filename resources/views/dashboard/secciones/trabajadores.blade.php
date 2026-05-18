<header class="section-header">
    <div class="header-info">
        <h1>Directorio de Trabajadores</h1>
        <p>Gestione la información laboral y el estatus institucional de los miembros activos y jubilados.</p>
    </div>
    <button class="btn-primary-dark">
        <i data-lucide="user-plus"></i> Agregar Trabajador
    </button>
</header>

<section class="filters-row">
    <div class="filter-group">
        <label>FILTRAR POR CARGO</label>
        <select><option>Todos los cargos</option></select>
    </div>
    <div class="filter-group">
        <label>FILTRAR POR ESTATUS</label>
        <select><option>Cualquier estatus</option></select>
    </div>
    <div class="filter-group">
        <label>TIPO DE NÓMINA</label>
        <select><option>Todas</option></select>
    </div>
    <div class="total-badge-card">
        <div>
            <p>TOTAL REGISTRADOS</p>
            <h2>1,248</h2>
        </div>
        <i data-lucide="users" class="icon-bg"></i>
    </div>
</section>

<div class="data-table-container">
    <table class="custom-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>NOMBRE COMPLETO</th>
                <th>CÉDULA</th>
                <th>CARGO</th>
                <th>TIPO</th>
                <th>ESTATUS</th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>TR-8821</td>
                <td>
                    <div class="user-cell">
                        <span class="avatar blue">AG</span>
                        <strong>Ana García</strong>
                    </div>
                </td>
                <td>12.345.678</td>
                <td>Profesora Titular</td>
                <td><span class="badge-type docente">DOCENTE</span></td>
                <td><span class="dot active"></span> Activo</td>
                <td class="actions">
                    <i data-lucide="folder-open"></i>
                    <i data-lucide="edit-3"></i>
                    <i data-lucide="trash-2"></i>
                </td>
            </tr>
            <tr>
                <td>TR-8822</td>
                <td>
                    <div class="user-cell">
                        <span class="avatar purple">RM</span>
                        <strong>Ricardo Mendoza</strong>
                    </div>
                </td>
                <td>10.122.334</td>
                <td>Analista de Sistemas</td>
                <td><span class="badge-type admin">ADMINISTRATIVO</span></td>
                <td><span class="dot jubilado"></span> Jubilado</td>
                <td class="actions">
                    <i data-lucide="folder-open"></i>
                    <i data-lucide="edit-3"></i>
                    <i data-lucide="trash-2"></i>
                </td>
            </tr>
        </tbody>
    </table>
    
    <div class="table-footer">
        <span>Mostrando 1 - 10 de 1,248 trabajadores</span>
        <div class="pagination">
            <button>&lt;</button>
            <button class="active">1</button>
            <button>2</button>
            <button>3</button>
            <span>...</span>
            <button>125</button>
            <button>&gt;</button>
        </div>
    </div>
</div>

<div class="content-layout">
    <div class="promo-card-blue">
        <div class="promo-content">
            <h3>Próximas Jubilaciones</h3>
            <p>Hay 14 docentes que cumplen los requisitos de años de servicio este trimestre. Inicie el proceso de revisión de expedientes.</p>
            <button class="btn-white">Ver Calendario</button>
        </div>
        <div class="promo-icon-watermark">
            <i data-lucide="scroll"></i>
        </div>
    </div>

    <div class="audit-card">
        <div class="audit-header">
            <i data-lucide="check-circle-2"></i> AUDITORÍA AL DÍA
        </div>
        <h3>Estatus de Datos</h3>
        <p>El 98.4% de los expedientes cuentan con documentación digitalizada completa.</p>
        <div class="progress-container">
            <div class="progress-bar" style="width: 98%;"></div>
        </div>
        <span class="progress-val">98%</span>
    </div>
</div>

<div id="modalTrabajador" class="modal-overlay">
    <div class="modal-container">
        <aside class="modal-sidebar">
            <span class="badge-new">Sigejub v1.0</span>
            <h1>Registrar<br>Nuevo<br>Trabajador</h1>
            <p>Complete el expediente institucional para iniciar el cálculo de antigüedad y estatus jubilatorio.</p>
            <div style="margin-top: auto; font-size: 0.75rem; color: #64748b;">
                <i data-lucide="info" style="width: 14px; vertical-align: middle;"></i> 
                Asegúrese de que la cédula sea exacta para evitar duplicados.
            </div>
        </aside>

        <main class="modal-form-content">
            <button class="btn-close-absolute" id="closeModal" type="button">&times;</button>
            
            <form id="formTrabajador" data-action="{{ route('trabajador') }}">
                @csrf 
                <section class="form-section">
                    <h3><i data-lucide="user"></i> Datos Personales</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>CÉDULA DE IDENTIDAD</label>
                            <input type="text" name="cedula" required placeholder="V-00000000">
                        </div>
                        <div class="input-group">
                            <label>GÉNERO</label>
                            <select name="genero" required>
                                <option value="" disabled selected>Seleccione...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>NOMBRES</label>
                            <input type="text" name="nombres" required>
                        </div>
                        <div class="input-group">
                            <label>APELLIDOS</label>
                            <input type="text" name="apellidos" required>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>FECHA DE NACIMIENTO</label>
                            <input type="date" name="fecha_nacimiento" required title="Necesario para calcular la edad automáticamente">
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i data-lucide="building"></i> Datos Institucionales</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>CARGO ACTUAL</label>
                            <input type="text" name="cargo" required>
                        </div>
                        <div class="input-group">
                            <label>UNIDAD O DEPARTAMENTO</label>
                            <input type="text" name="unidad_departamento" required>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>GRADO / NIVEL</label>
                            <input type="text" name="grado_nivel" placeholder="Ej: P1, B1..." required>
                        </div>
                        <div class="input-group">
                            <label>FECHA DE INGRESO</label>
                            <input type="date" name="fecha_ingreso" required title="Necesario para calcular antigüedad institucional">
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>AÑOS ADM. PÚBLICA (EXTERNO)</label>
                            <input type="number" name="anos_servicio_externo" value="0" min="0">
                        </div>
                        <div class="input-group">
                            <label>% ANTIGÜEDAD (OPCIONAL)</label>
                            <input type="number" step="0.01" name="porcentaje_antiguedad" value="0">
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i data-lucide="graduation-cap"></i> Información Socio-Económica</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>NIVEL DE INSTRUCCIÓN</label>
                            <select name="nivel_instruccion">
                                <option value="1">TSU</option>
                                <option value="2">Licenciado / Ingeniero</option>
                                <option value="3">Especialista</option>
                                <option value="4">Magíster</option>
                                <option value="5">Doctorado</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>NÚMERO DE CUENTA (BDV)</label>
                            <input type="text" name="cuenta_bancaria" placeholder="0102..." pattern="\d{20}" title="Deben ser 20 dígitos">
                        </div>
                    </div>
                </section>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="btnCancelar">Descartar</button>
                    <button type="submit" class="btn-submit">Registrar Trabajador</button>
                </div>
            </form>
        </main>
    </div>
</div>