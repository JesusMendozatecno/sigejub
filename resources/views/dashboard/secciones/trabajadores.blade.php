{{-- trabajadores.blade.php - Sección de directorio de trabajadores: tabla DataTable con filtros, modal de registro/edición con formulario completo y modal de confirmación de baja. --}}
<header class="section-header">
    <div class="header-info">
        <h1>Directorio de Trabajadores</h1>
        <p>Gestione la información laboral y el estatus institucional de los miembros activos y jubilados.</p>
    </div>
    <div class="header-actions">
        <button type="button" class="btn-primary-dark" id="btnRegistrarTrabajador">
            <i class="fas fa-circle-plus" size="20"></i> Registrar Trabajador
        </button>
    </div>
</header>

<section class="filters-bar-card" style="margin-top: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
    <div class="filter-group">
        <label>FILTRAR POR ESTATUS</label>
        <select id="filtroEstatus">
            <option value="">Cualquier estatus</option>
            <option value="activo">Activo</option>
            <option value="jubilado">Jubilado</option>
        </select>
    </div>
    <div class="filter-group">
        <label>TIPO DE NÓMINA</label>
        <select id="filtroNomina">
            <option value="">Todas</option>
            <option value="Docente">Docente</option>
            <option value="Administrativo">Administrativo</option>
            <option value="Obrero">Obrero</option>
        </select>
    </div>
    <div class="total-badge-card" style="margin-left: auto;">
        <div>
            <p>TOTAL REGISTRADOS</p>
            <h2 id="totalTrabajadores">0</h2>
        </div>
        <i class="fas fa-users icon-bg"></i>
    </div>
</section>

<div class="data-table-container" style="margin-top: 20px;">
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
        <tbody id="tbodyTrabajadores">
            </tbody>
    </table>
    
    <div class="table-footer">
        <span>Mostrando registros en tiempo real</span>
        <div class="pagination">
            <button type="button">&lt;</button>
            <button type="button" class="active">1</button>
            <button type="button">&gt;</button>
        </div>
    </div>
</div>

<div class="content-layout" style="margin-top: 20px;">
    <div class="promo-card-blue">
        <div class="promo-content">
            <h3>Próximas Jubilaciones</h3>
            <div id="proximasJubilacionesList">
                <p style="color:rgba(255,255,255,0.7);font-size:0.85rem;">Cargando...</p>
            </div>
            <button class="btn-white" type="button" style="margin-top:12px;">Ver Calendario</button>
        </div>
        <div class="promo-icon-watermark">
            <i class="fas fa-scroll"></i>
        </div>
    </div>

    <div class="audit-card">
        <div class="audit-header">
            <i class="fas fa-circle-check"></i> AUDITORÍA AL DÍA
        </div>
        <h3>Estatus de Datos</h3>
        <p id="estatusDatosTexto">Cargando estadísticas...</p>
        <div class="progress-container">
            <div class="progress-bar" id="estatusDatosBarra" style="width: 0%;"></div>
        </div>
        <span class="progress-val" id="estatusDatosPorcentaje">0%</span>
    </div>
</div>

<div id="modalTrabajador" class="modal-overlay">
    <div class="modal-container">
        <aside class="modal-sidebar">
            <span class="badge-new">Sigejub v1.0</span>
            <h1 id="modalTitle">Registrar<br>Nuevo<br>Trabajador</h1>
            <p id="modalDescription">Complete el expediente institucional para iniciar el cálculo de antigüedad y estatus jubilatorio.</p>
            <div class="sidebar-actions">
                <button type="button" class="btn-sidebar-edit" id="btnHabilitarEdicion" style="display: none;">
                    <i class="fas fa-pen"></i> Editar Expediente
                </button>
                <button type="button" class="btn-sidebar-cancel" id="btnCancelar">Descartar</button>
            </div>
        </aside>

        <main class="modal-form-content">
            <button class="btn-close-absolute" id="closeModal" type="button">&times;</button>
            
            <form id="formTrabajador">
                @csrf
                <section class="form-section">
                    <h3><i class="fas fa-user"></i> Datos Personales</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>CÉDULA DE IDENTIDAD</label>
                            <input type="text" name="cedula" id="inputCedula" required placeholder="V-00000000">
                        </div>
                        <div class="input-group">
                            <label>GÉNERO</label>
                            <select name="genero" id="selectGenero" required>
                                <option value="" disabled selected>Seleccione...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>NOMBRES</label>
                            <input type="text" name="nombres" id="inputNombres" required>
                        </div>
                        <div class="input-group">
                            <label>APELLIDOS</label>
                            <input type="text" name="apellidos" id="inputApellidos" required>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>FECHA DE NACIMIENTO</label>
                            <input type="date" name="fecha_nacimiento" id="inputFechaNacimiento" required>
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i class="fas fa-building"></i> Datos Institucionales</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>CARGO ACTUAL</label>
                            <input type="text" name="cargo" id="inputCargo" required>
                        </div>
                        <div class="input-group">
                            <label>UNIDAD O DEPARTAMENTO</label>
                            <input type="text" name="unidad_departamento" id="inputUnidadDepartamento" required>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>GRADO / NIVEL</label>
                            <input type="text" name="grado_nivel" id="inputGradoNivel" placeholder="Ej: P1, B1..." required>
                        </div>
                        <div class="input-group">
                            <label>FECHA DE INGRESO</label>
                            <input type="date" name="fecha_ingreso" id="inputFechaIngreso" required>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>AÑOS ADM. PÚBLICA (EXTERNO)</label>
                            <input type="number" name="anos_servicio_externo" id="inputAnosExterno" value="0" min="0">
                        </div>
                        <div class="input-group">
                            <label>% ANTIGÜEDAD (OPCIONAL)</label>
                            <input type="number" step="0.01" name="porcentaje_antiguedad" id="inputPorcentajeAntiguedad" value="0">
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i class="fas fa-graduation-cap"></i> Información Socio-Económica</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>NIVEL DE INSTRUCCIÓN</label>
                            <select name="nivel_instruccion" id="selectNivelInstruccion">
                                <option value="1">TSU</option>
                                <option value="2">Licenciado / Ingeniero</option>
                                <option value="3">Especialista</option>
                                <option value="4">Magíster</option>
                                <option value="5">Doctorado</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>NÚMERO DE CUENTA (BDV)</label>
                            <input type="text" name="cuenta_bancaria" id="inputCuentaBancaria" placeholder="0102..." pattern="\d{20}">
                        </div>
                    </div>
                </section>

                <div class="modal-actions">
                    <button type="submit" class="btn-submit" id="btnSubmitTrabajador">Registrar Trabajador</button>
                </div>
            </form>
        </main>
    </div>
</div>

<div id="modalEliminarConfirm" class="modal-overlay" style="z-index: 1100;">
    <div class="modal-delete-box">
        <div class="delete-icon-warn">
            <i class="fas fa-triangle-exclamation"></i>
        </div>
        <h3>¿Confirmar baja del trabajador?</h3>
        <p>Esta acción ejecutará un soft-delete institucional sobre <strong id="deleteWorkerName"></strong>. ¿Desea continuar?</p>
        <div class="delete-actions">
            <button type="button" class="btn-delete-cancel" id="btnNoEliminar">No, cancelar</button>
            <button type="button" class="btn-delete-confirm" id="btnSiEliminar">Sí, eliminar</button>
        </div>
    </div>
</div>