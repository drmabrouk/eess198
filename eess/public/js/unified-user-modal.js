/**
 * EESS Unified User & Employee Management Modal Controller
 * Synchronizes client-side behavior across System User Management, HR, and Employee Profile
 */

var eessCurrentStep = 1;
var eessIsEditMode = false;
var eessAjaxUrl = (typeof ajaxurl !== 'undefined') ? ajaxurl : '/wp-admin/admin-ajax.php';

window.eessOpenUnifiedUserModal = function(mode, userId) {
    mode = mode || 'add_user';
    userId = userId || 0;

    eessIsEditMode = (mode === 'edit_user' || mode === 'edit_employee_profile') && parseInt(userId) > 0;

    var modal = document.getElementById('unified-user-modal');
    if (!modal) return;

    var form = document.getElementById('eess-unified-user-form');
    if (form) form.reset();

    document.getElementById('u_user_id').value = userId;
    document.getElementById('u_form_mode').value = mode;

    // Reset error labels
    var errors = document.querySelectorAll('.eess-field-error');
    errors.forEach(function(el) { el.style.display = 'none'; });

    // Set title according to mode
    var titleEl = document.getElementById('u_modal_title');
    if (titleEl) {
        if (mode === 'add_user') titleEl.innerText = '➕ إضافة مستخدم جديد في النظام';
        else if (mode === 'add_employee') titleEl.innerText = '➕ إضافة موظف جديد بملف الموارد البشرية';
        else if (mode === 'edit_employee_profile') titleEl.innerText = '⚙️ تعديل وتزامن معلومات الموظف والحساب';
        else titleEl.innerText = '✏️ تعديل بيانات حساب وتعيينات الموظف';
    }

    // Passwords & Change Password Toggle
    var passRow = document.getElementById('u_password_row');
    var passToggleBtn = document.getElementById('u_change_pass_toggle_container');
    var passReq = document.getElementById('u_pass_req');
    var passConfReq = document.getElementById('u_pass_confirm_req');
    var passInput = document.getElementById('u_user_pass');
    var passConfInput = document.getElementById('u_user_pass_confirm');

    if (eessIsEditMode) {
        if (passRow) passRow.style.display = 'none';
        if (passToggleBtn) passToggleBtn.style.display = 'block';
        if (passReq) passReq.style.display = 'none';
        if (passConfReq) passConfReq.style.display = 'none';
        if (passInput) passInput.required = false;
        if (passConfInput) passConfInput.required = false;
        document.getElementById('u_username').readOnly = true;
    } else {
        if (passRow) passRow.style.display = 'grid';
        if (passToggleBtn) passToggleBtn.style.display = 'none';
        if (passReq) passReq.style.display = 'inline';
        if (passConfReq) passConfReq.style.display = 'inline';
        if (passInput) passInput.required = true;
        if (passConfInput) passConfInput.required = true;
        document.getElementById('u_username').readOnly = false;
    }

    // Clear Avatar Preview
    var photoPreview = document.getElementById('u_photo_preview');
    if (photoPreview) {
        photoPreview.src = 'data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%2394a3b8\'><path d=\'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z\'/></svg>';
    }

    // Go to Step 1
    eessGoToStep(1);

    // If Edit Mode, Fetch User Data
    if (eessIsEditMode) {
        eessLoadUserData(userId);
    }

    modal.style.display = 'flex';
};

window.eessCloseUnifiedUserModal = function() {
    var modal = document.getElementById('unified-user-modal');
    if (modal) modal.style.display = 'none';
};

window.eessGoToStep = function(step) {
    if (step === 2) {
        if (!eessValidateStep1()) return;
    }

    eessCurrentStep = step;

    var step1Container = document.getElementById('u_step_1_container');
    var step2Container = document.getElementById('u_step_2_container');
    var ind1 = document.getElementById('u_indicator_step1');
    var ind2 = document.getElementById('u_indicator_step2');
    var btnPrev = document.getElementById('u_btn_prev');
    var btnNext = document.getElementById('u_btn_next');
    var btnSave = document.getElementById('u_btn_save');

    if (step === 1) {
        step1Container.style.display = 'block';
        step2Container.style.display = 'none';

        ind1.style.background = 'var(--sm-primary-color, #1e293b)';
        ind1.style.color = 'white';
        ind2.style.background = '#f1f5f9';
        ind2.style.color = '#64748b';

        btnPrev.style.display = 'none';
        btnNext.style.display = 'inline-block';
        btnSave.style.display = 'none';
    } else {
        step1Container.style.display = 'none';
        step2Container.style.display = 'block';

        ind1.style.background = '#16a34a';
        ind1.style.color = 'white';
        ind2.style.background = 'var(--sm-primary-color, #1e293b)';
        ind2.style.color = 'white';

        btnPrev.style.display = 'inline-block';
        btnNext.style.display = 'none';
        btnSave.style.display = 'inline-block';

        eessOnRoleChanged();
    }
};

window.eessToggleChangePassword = function() {
    var passRow = document.getElementById('u_password_row');
    if (passRow) {
        if (passRow.style.display === 'none' || passRow.style.display === '') {
            passRow.style.display = 'grid';
        } else {
            passRow.style.display = 'none';
        }
    }
};

window.eessSyncUsername = function(empInput) {
    if (!empInput) return;
    var clean = empInput.value.replace(/^(EMP|EMP-|_)+/i, '').trim();
    empInput.value = clean;
    var usernameInput = document.getElementById('u_username');
    if (usernameInput) usernameInput.value = clean;
    var errEl = document.getElementById('err_u_employee_id');
    if (errEl && clean !== '') errEl.style.display = 'none';
};

window.eessPreviewAvatar = function(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var photoPreview = document.getElementById('u_photo_preview');
            if (photoPreview) photoPreview.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
};

window.eessValidateField = function(input) {
    if (!input) return;
    var errEl = document.getElementById('err_' + input.id);
    if (input.checkValidity() && input.value.trim() !== '') {
        if (errEl) errEl.style.display = 'none';
    }
};

window.eessValidateStep1 = function() {
    var valid = true;
    var firstName = document.getElementById('u_first_name');
    var lastName = document.getElementById('u_last_name');
    var username = document.getElementById('u_username');
    var email = document.getElementById('u_user_email');
    var phone = document.getElementById('u_phone_number');
    var empId = document.getElementById('u_employee_id');
    var pass = document.getElementById('u_user_pass');
    var passConf = document.getElementById('u_user_pass_confirm');
    var passRow = document.getElementById('u_password_row');

    if (!firstName.value.trim()) {
        document.getElementById('err_u_first_name').style.display = 'block';
        valid = false;
    } else { document.getElementById('err_u_first_name').style.display = 'none'; }

    if (!lastName.value.trim()) {
        document.getElementById('err_u_last_name').style.display = 'block';
        valid = false;
    } else { document.getElementById('err_u_last_name').style.display = 'none'; }

    if (!username.value.trim() && empId.value.trim()) {
        username.value = empId.value.trim();
    }

    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email.value.trim())) {
        document.getElementById('err_u_user_email').style.display = 'block';
        valid = false;
    } else { document.getElementById('err_u_user_email').style.display = 'none'; }

    if (!phone.value.trim()) {
        document.getElementById('err_u_phone_number').style.display = 'block';
        valid = false;
    } else { document.getElementById('err_u_phone_number').style.display = 'none'; }

    if (!empId.value.trim()) {
        document.getElementById('err_u_employee_id').style.display = 'block';
        valid = false;
    } else { document.getElementById('err_u_employee_id').style.display = 'none'; }

    // Validate password if visible
    if (passRow && passRow.style.display !== 'none') {
        if (!eessIsEditMode && pass.value.length < 6) {
            document.getElementById('err_u_user_pass').style.display = 'block';
            valid = false;
        } else { document.getElementById('err_u_user_pass').style.display = 'none'; }

        if (pass.value !== passConf.value) {
            document.getElementById('err_u_user_pass_confirm').style.display = 'block';
            valid = false;
        } else { document.getElementById('err_u_user_pass_confirm').style.display = 'none'; }
    }

    return valid;
};

window.eessCheckUniqueness = function(field) {
    var userId = document.getElementById('u_user_id').value;
    var val = '';
    if (field === 'username') val = document.getElementById('u_username').value.trim();
    if (field === 'email') val = document.getElementById('u_user_email').value.trim();
    if (field === 'employee_id') val = document.getElementById('u_employee_id').value.trim();

    if (!val) return;

    var nonceEl = document.querySelector('#eess-unified-user-form [name="sm_nonce"]');
    var nonce = nonceEl ? nonceEl.value : '';

    var formData = new FormData();
    formData.append('action', 'eess_check_user_uniqueness');
    formData.append('sm_nonce', nonce);
    formData.append('field', field);
    formData.append('value', val);
    formData.append('user_id', userId);

    fetch(eessAjaxUrl, { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success && res.data.exists) {
            var errEl = document.getElementById('err_u_' + (field === 'email' ? 'user_email' : field));
            if (errEl) {
                errEl.innerText = res.data.message || 'القيمة مُستخدمة سابقاً في النظام.';
                errEl.style.display = 'block';
            }
        }
    })
    .catch(function(e) { console.error(e); });
};

window.eessOnRoleChanged = function() {
    var role = document.getElementById('u_user_role').value;
    var subjWrapper = document.getElementById('u_subject_wrapper');
    var deptWrapper = document.getElementById('u_department_wrapper');

    if (role === 'sm_teacher' || role === 'teachers' || role === 'sm_hod') {
        if (subjWrapper) subjWrapper.style.display = 'block';
    } else {
        if (subjWrapper) subjWrapper.style.display = 'none';
    }

    if (role === 'sm_principal' || role === 'school_manager' || role === 'administrator') {
        if (deptWrapper) deptWrapper.style.display = 'none';
    } else {
        if (deptWrapper) deptWrapper.style.display = 'block';
    }
};

window.eessOnScopeChanged = function() {
    var scope = document.getElementById('u_access_scope').value;
    var schoolWrapper = document.getElementById('u_school_wrapper');
    var schoolSelect = document.getElementById('u_school_id');

    if (scope === 'institution') {
        if (schoolSelect) schoolSelect.required = false;
    } else {
        if (schoolSelect) schoolSelect.required = true;
    }
};

window.eessOnInstitutionChanged = function() {
    var instId = document.getElementById('u_institution_id').value;
    var schoolSelect = document.getElementById('u_school_id');

    if (!schoolSelect) return;

    for (var i = 0; i < schoolSelect.options.length; i++) {
        var opt = schoolSelect.options[i];
        if (!opt.value) continue;

        var optInst = opt.getAttribute('data-institution');
        if (!instId || optInst === instId) {
            opt.style.display = 'block';
        } else {
            opt.style.display = 'none';
        }
    }
};

window.eessOnSchoolChanged = function() {
    // Dynamic school updates if required
};

window.eessLoadUserData = function(userId) {
    var nonceEl = document.querySelector('#eess-unified-user-form [name="sm_nonce"]');
    var nonce = nonceEl ? nonceEl.value : '';

    var formData = new FormData();
    formData.append('action', 'eess_get_user_unified');
    formData.append('sm_nonce', nonce);
    formData.append('user_id', userId);

    fetch(eessAjaxUrl, { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success && res.data) {
            var u = res.data;
            document.getElementById('u_first_name').value = u.first_name || '';
            document.getElementById('u_last_name').value = u.last_name || '';
            var cleanEmpId = (u.employee_id || u.user_login || '').replace(/^(EMP|EMP-|_)+/i, '').trim();
            document.getElementById('u_employee_id').value = cleanEmpId;
            document.getElementById('u_username').value = cleanEmpId;
            document.getElementById('u_user_email').value = u.user_email || '';
            if (u.country_code) {
                var cc = document.getElementById('u_country_code');
                if (cc) cc.value = u.country_code;
            }
            document.getElementById('u_phone_number').value = u.phone_number || '';
            document.getElementById('u_user_status').value = u.user_status || 'active';
            document.getElementById('u_civil_id').value = u.civil_id || '';
            var normalizedRole = u.role || 'sm_teacher';
            if (normalizedRole === 'teachers') normalizedRole = 'sm_teacher';
            if (normalizedRole === 'school_manager') normalizedRole = 'sm_principal';
            if (normalizedRole === 'educational_supervisor') normalizedRole = 'sm_supervisor';
            if (normalizedRole === 'clinic') normalizedRole = 'sm_clinic';
            if (normalizedRole === 'accountant') normalizedRole = 'sm_accountant';

            document.getElementById('u_user_role').value = normalizedRole;
            document.getElementById('u_access_scope').value = u.access_scope || 'school';
            document.getElementById('u_institution_id').value = u.institution_id || '';
            document.getElementById('u_school_id').value = u.school_id || '';
            document.getElementById('u_department').value = u.department || '';
            document.getElementById('u_specialization').value = u.specialization || '';
            document.getElementById('u_official_title').value = u.official_title || '';

            if (u.photo_url) {
                document.getElementById('u_photo_preview').src = u.photo_url;
            }

            eessOnInstitutionChanged();
            eessOnRoleChanged();
        }
    })
    .catch(function(e) { console.error(e); });
};

window.eessSubmitUnifiedUserForm = function() {
    var role = document.getElementById('u_user_role').value;
    var inst = document.getElementById('u_institution_id').value;

    if (!role) {
        document.getElementById('err_u_user_role').style.display = 'block';
        return;
    } else { document.getElementById('err_u_user_role').style.display = 'none'; }

    if (!inst) {
        document.getElementById('err_u_institution_id').style.display = 'block';
        return;
    } else { document.getElementById('err_u_institution_id').style.display = 'none'; }

    var saveBtn = document.getElementById('u_btn_save');
    saveBtn.innerText = '⏳ جاري الحفظ والتزامن...';
    saveBtn.disabled = true;

    var form = document.getElementById('eess-unified-user-form');
    var formData = new FormData(form);
    formData.append('action', 'eess_save_user_unified');

    fetch(eessAjaxUrl, { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            alert('✅ ' + (res.data.message || 'تم حفظ وتزامن بيانات الموظف بنجاح في المنصة الرقمية.'));
            eessCloseUnifiedUserModal();
            location.reload();
        } else {
            alert('❌ خطأ: ' + (res.data || 'حدث خطأ أثناء حفظ البيانات.'));
            saveBtn.innerText = '💾 حفظ وتزامن البيانات';
            saveBtn.disabled = false;
        }
    })
    .catch(function(err) {
        alert('❌ حدث خطأ غير متوقع في الاتصال بالسيرفر.');
        saveBtn.innerText = '💾 حفظ وتزامن البيانات';
        saveBtn.disabled = false;
    });
};
