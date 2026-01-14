// Admin Dashboard JavaScript
// Check if user is admin
const user = checkRole('admin');

// Initialize dashboard
document.addEventListener('DOMContentLoaded', () => {
    initUserAvatar('userAvatar', user.email);
    document.getElementById('userName').textContent = 'Administrator';
    document.getElementById('userEmail').textContent = user.email;
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    loadDashboard();

    // Auto-refresh dashboard stats every 30 seconds
    setInterval(() => {
        loadDashboard();
        console.log('Dashboard stats refreshed automatically');
    }, 30000); // 30 seconds
});

// Section management
function showSection(section) {
    // Hide all sections
    document.querySelectorAll('section').forEach(s => s.style.display = 'none');

    // Update menu active state
    document.querySelectorAll('.sidebar-menu-link').forEach(link => {
        link.classList.remove('active');
    });

    // Show selected section
    switch (section) {
        case 'doctors':
            document.getElementById('doctorsSection').style.display = 'block';
            loadDoctors();
            break;
        case 'schedule':
            document.getElementById('scheduleSection').style.display = 'block';
            loadSessions();
            loadDoctorsForSelector();
            break;
        case 'appointments':
            document.getElementById('appointmentsSection').style.display = 'block';
            loadAppointments();
            break;
        case 'patients':
            document.getElementById('patientsSection').style.display = 'block';
            loadPatients();
            break;
        default:
            document.getElementById('dashboardSection').style.display = 'block';
            loadDashboard();
    }
}

// Load dashboard stats
async function loadDashboard() {
    try {
        const stats = await apiRequest('../api/admin/stats.php');
        if (stats.success) {
            animateValue('doctorCount', 0, stats.stats.doctors, 1000);
            animateValue('patientCount', 0, stats.stats.patients, 1000);
        }

        // Load appointments and sessions for dashboard
        loadUpcomingAppointments();
        loadUpcomingSessions();
    } catch (error) {
        console.error('Failed to load dashboard:', error);
    }
}

// Animate number counting
function animateValue(id, start, end, duration) {
    const element = document.getElementById(id);
    const range = end - start;
    const increment = end > start ? 1 : -1;
    const stepTime = Math.abs(Math.floor(duration / range));
    let current = start;

    const timer = setInterval(() => {
        current += increment;
        element.textContent = current;
        if (current === end) {
            clearInterval(timer);
        }
    }, stepTime);
}

// Load upcoming appointments
async function loadUpcomingAppointments() {
    try {
        const result = await apiRequest('../api/admin/appointments.php');
        const tbody = document.getElementById('upcomingAppointmentsBody');

        if (result.success && result.appointments.length > 0) {
            const upcoming = result.appointments
                .filter(apt => new Date(apt.scheduled_date) >= new Date())
                .slice(0, 5);

            tbody.innerHTML = upcoming.map(apt => `
                <tr>
                    <td>${apt.appointment_number}</td>
                    <td>${apt.patient_name}</td>
                    <td>${apt.doctor_name}</td>
                    <td>${apt.session_title}</td>
                    <td><span class="badge ${getStatusBadge(apt.status)}">${apt.status}</span></td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No upcoming appointments</td></tr>';
        }
    } catch (error) {
        console.error('Failed to load appointments:', error);
    }
}

// Load upcoming sessions
async function loadUpcomingSessions() {
    try {
        const result = await apiRequest('../api/admin/sessions.php');
        const tbody = document.getElementById('upcomingSessionsBody');

        if (result.success && result.sessions.length > 0) {
            const upcoming = result.sessions
                .filter(session => new Date(session.scheduled_date) >= new Date())
                .slice(0, 5);

            tbody.innerHTML = upcoming.map(session => `
                <tr>
                    <td>${session.title}</td>
                    <td>${session.doctor_name}</td>
                    <td>${formatDateTime(session.scheduled_date, session.scheduled_time)}</td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No upcoming sessions</td></tr>';
        }
    } catch (error) {
        console.error('Failed to load sessions:', error);
    }
}

// Load doctors
async function loadDoctors() {
    try {
        const result = await apiRequest('../api/admin/doctors.php');
        const tbody = document.getElementById('doctorsTableBody');

        if (result.success && result.doctors.length > 0) {
            document.getElementById('doctorListCount').textContent = result.doctors.length;

            tbody.innerHTML = result.doctors.map(doctor => `
                <tr>
                    <td><strong>${doctor.name}</strong></td>
                    <td>${doctor.email}</td>
                    <td><span class="badge badge-primary">${doctor.specialty}</span></td>
                    <td>
                        <button class="btn btn-sm btn-secondary" onclick="viewDoctor(${doctor.id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn btn-sm btn-error" onclick="deleteDoctor(${doctor.id}, '${doctor.name}')">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No doctors found</td></tr>';
        }
    } catch (error) {
        console.error('Failed to load doctors:', error);
    }
}

// Add doctor form handler
document.getElementById('addDoctorForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const data = {
        name: document.getElementById('doctorName').value,
        email: document.getElementById('doctorEmail').value,
        password: document.getElementById('doctorPassword').value,
        specialty: document.getElementById('doctorSpecialty').value,
        phone: document.getElementById('doctorPhone').value || null
    };

    setLoadingState('addDoctorBtn', true, 'Adding...');

    try {
        const result = await apiRequest('../api/admin/doctors.php', 'POST', data);

        if (result.success) {
            showNotification('Doctor added successfully!', 'success');
            closeModal('addDoctorModal');
            document.getElementById('addDoctorForm').reset();
            loadDoctors();
            loadDashboard();
        } else {
            showNotification(result.message || 'Failed to add doctor', 'error');
        }
    } catch (error) {
        showNotification('An error occurred', 'error');
        console.error('Add doctor error:', error);
    } finally {
        setLoadingState('addDoctorBtn', false);
    }
});

// Delete doctor
async function deleteDoctor(id, name) {
    if (!confirm(`Are you sure you want to delete Dr. ${name}?`)) return;

    try {
        const result = await apiRequest('../api/admin/doctors.php', 'DELETE', { id });

        if (result.success) {
            showNotification('Doctor deleted successfully!', 'success');
            loadDoctors();
            loadDashboard();
        } else {
            showNotification(result.message || 'Failed to delete doctor', 'error');
        }
    } catch (error) {
        showNotification('An error occurred', 'error');
        console.error('Delete doctor error:', error);
    }
}

// View doctor (placeholder)
function viewDoctor(id) {
    showNotification('View doctor details - Feature coming soon!', 'success');
}

// Load doctors for session selector
async function loadDoctorsForSelector() {
    try {
        const result = await apiRequest('../api/admin/doctors.php');
        const select = document.getElementById('sessionDoctor');

        if (result.success && result.doctors.length > 0) {
            select.innerHTML = '<option value="">Choose Doctor from the list</option>' +
                result.doctors.map(doctor =>
                    `<option value="${doctor.id}">${doctor.name} - ${doctor.specialty}</option>`
                ).join('');
        }
    } catch (error) {
        console.error('Failed to load doctors:', error);
    }
}

// Load sessions
async function loadSessions() {
    try {
        const result = await apiRequest('../api/admin/sessions.php');
        const tbody = document.getElementById('sessionsTableBody');

        if (result.success && result.sessions.length > 0) {
            document.getElementById('sessionListCount').textContent = result.sessions.length;

            tbody.innerHTML = result.sessions.map(session => `
                <tr>
                    <td><strong>${session.title}</strong></td>
                    <td>${session.doctor_name}<br><span class="badge badge-primary">${session.specialty}</span></td>
                    <td>${formatDateTime(session.scheduled_date, session.scheduled_time)}</td>
                    <td>${session.current_bookings} / ${session.max_bookings}</td>
                    <td>
                        <button class="btn btn-sm btn-secondary" onclick="viewSession(${session.id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn btn-sm btn-error" onclick="deleteSession(${session.id}, '${session.title}')">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No sessions found</td></tr>';
        }
    } catch (error) {
        console.error('Failed to load sessions:', error);
    }
}

// Add session form handler
document.getElementById('addSessionForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const data = {
        doctor_id: parseInt(document.getElementById('sessionDoctor').value),
        title: document.getElementById('sessionTitle').value,
        scheduled_date: document.getElementById('sessionDate').value,
        scheduled_time: document.getElementById('sessionTime').value,
        max_bookings: parseInt(document.getElementById('sessionMaxBookings').value)
    };

    setLoadingState('addSessionBtn', true, 'Adding...');

    try {
        const result = await apiRequest('../api/admin/sessions.php', 'POST', data);

        if (result.success) {
            showNotification('Session created successfully!', 'success');
            closeModal('addSessionModal');
            document.getElementById('addSessionForm').reset();
            loadSessions();
            loadDashboard();
        } else {
            showNotification(result.message || 'Failed to create session', 'error');
        }
    } catch (error) {
        showNotification('An error occurred', 'error');
        console.error('Add session error:', error);
    } finally {
        setLoadingState('addSessionBtn', false);
    }
});

// Delete session
async function deleteSession(id, title) {
    if (!confirm(`Are you sure you want to delete session "${title}"?`)) return;

    try {
        const result = await apiRequest('../api/admin/sessions.php', 'DELETE', { id });

        if (result.success) {
            showNotification('Session deleted successfully!', 'success');
            loadSessions();
            loadDashboard();
        } else {
            showNotification(result.message || 'Failed to delete session', 'error');
        }
    } catch (error) {
        showNotification('An error occurred', 'error');
        console.error('Delete session error:', error);
    }
}

// View session (placeholder)
function viewSession(id) {
    showNotification('View session details - Feature coming soon!', 'success');
}

// Load appointments
async function loadAppointments() {
    try {
        const result = await apiRequest('../api/admin/appointments.php');
        const tbody = document.getElementById('appointmentsTableBody');

        if (result.success && result.appointments.length > 0) {
            document.getElementById('appointmentListCount').textContent = result.appointments.length;

            tbody.innerHTML = result.appointments.map(apt => `
                <tr>
                    <td><strong>#${apt.appointment_number}</strong></td>
                    <td>${apt.patient_name}<br><small class="text-muted">${apt.patient_phone || 'N/A'}</small></td>
                    <td>${apt.doctor_name}<br><span class="badge badge-primary">${apt.specialty}</span></td>
                    <td>${apt.session_title}</td>
                    <td>${formatDateTime(apt.scheduled_date, apt.scheduled_time)}</td>
                    <td><span class="badge ${getStatusBadge(apt.status)}">${apt.status}</span></td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No appointments found</td></tr>';
        }
    } catch (error) {
        console.error('Failed to load appointments:', error);
    }
}

// Load patients
async function loadPatients() {
    try {
        const result = await apiRequest('../api/admin/patients.php');
        const tbody = document.getElementById('patientsTableBody');

        if (result.success && result.patients.length > 0) {
            document.getElementById('patientListCount').textContent = result.patients.length;

            tbody.innerHTML = result.patients.map(patient => `
                <tr>
                    <td><strong>${patient.name}</strong></td>
                    <td>${patient.email}</td>
                    <td>${patient.phone || 'N/A'}</td>
                    <td>${patient.date_of_birth ? formatDate(patient.date_of_birth) : 'N/A'}</td>
                    <td>${formatDate(patient.created_at)}</td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No patients found</td></tr>';
        }
    } catch (error) {
        console.error('Failed to load patients:', error);
    }
}
