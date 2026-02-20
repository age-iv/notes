import React, { useState, useEffect } from 'react';
import './App.css';

function App() {
    // Состояния для заметок и загрузки
    const [notes, setNotes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // Состояния для формы
    const [title, setTitle] = useState('');
    const [content, setContent] = useState('');
    const [editId, setEditId] = useState(null);
    const URL = 'http://localhost:8080';

    // Состояние для сообщений
    const [message, setMessage] = useState({ type: '', text: '' });

    // Загрузка заметок при монтировании компонента
    useEffect(() => {
        fetchNotes();
    }, []);

    // Функция для отображения сообщений
    const showMessage = (text, type = 'success') => {
        setMessage({ text, type });
        setTimeout(() => setMessage({ type: '', text: '' }), 3000);
    };

    // Загрузка заметок с API
    const fetchNotes = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await fetch(URL + '/api/notes/');

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            // Обработка разных форматов ответа
            let notesArray = [];

            if (Array.isArray(data)) {
                // Старый формат: прямой массив
                notesArray = data;
            } else if (data && Array.isArray(data.data)) {
                // Новый формат: { data: [...] }
                notesArray = data.data;
            } else if (data && data.data && Array.isArray(data.data)) {
                // Другой возможный формат
                notesArray = data.data;
            } else {
                // Если формат неизвестен, пробуем извлечь массив
                console.warn('Unknown response format:', data);
                notesArray = [];
            }

            console.log('Loaded notes:', notesArray.length);
            setNotes(notesArray);

        } catch (error) {
            console.error('Error fetching notes:', error);
            setError('Failed to load notes. Please check if the API server is running.');
            showMessage('Failed to load notes', 'error');
            setNotes([]);
        } finally {
            setLoading(false);
        }
    };

    // Создание или обновление заметки
    const saveNote = async () => {
        // Валидация
        if (!title.trim() || title.length < 3) {
            showMessage('Title must be at least 3 characters', 'error');
            return;
        }

        if (!content.trim()) {
            showMessage('Content is required', 'error');
            return;
        }

        const noteData = { title, content };

        try {
            let response;
            let method;
            let url;

            if (editId) {
                // Обновление существующей заметки
                method = 'PUT';
                url = URL + `/api/notes/${editId}`;
            } else {
                // Создание новой заметки
                method = 'POST';
                url = URL + '/api/notes/';
            }

            response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(noteData)
            });

            console.log(response);

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.error || `Failed to save note (${response.status})`);
            }

            // Успешное сохранение
            if (editId) {
                showMessage('Note updated successfully', 'success');
            } else {
                showMessage('Note created successfully', 'success');
            }

            // Сброс формы
            setTitle('');
            setContent('');
            setEditId(null);

            // Обновление списка заметок
            await fetchNotes();

        } catch (error) {
            console.error('Error saving note:', error);
            showMessage(error.message || 'Failed to save note', 'error');
        }
    };

    // Удаление заметки
    const deleteNote = async (id) => {
        if (!window.confirm('Are you sure you want to delete this note?')) {
            return;
        }

        try {
            const response = await fetch(URL + `/api/notes/${id}`, {
                method: 'DELETE'
            });

            if (!response.ok) {
                throw new Error(`Failed to delete note (${response.status})`);
            }

            showMessage('Note deleted successfully', 'success');

            // Обновление списка заметок
            await fetchNotes();

        } catch (error) {
            console.error('Error deleting note:', error);
            showMessage(error.message || 'Failed to delete note', 'error');
        }
    };

    // Редактирование заметки (заполнение формы)
    const editNote = (note) => {
        setTitle(note.title || '');
        setContent(note.content || '');
        setEditId(note.id);

        // Прокрутка к форме
        setTimeout(() => {
            const formElement = document.getElementById('note-form');
            if (formElement) {
                formElement.scrollIntoView({ behavior: 'smooth' });
            }
        }, 100);
    };

    // Отмена редактирования
    const cancelEdit = () => {
        setTitle('');
        setContent('');
        setEditId(null);
    };

    // Форматирование даты
    const formatDate = (dateString) => {
        if (!dateString) return 'Unknown date';

        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (error) {
            return dateString;
        }
    };

    // Обрезка длинного текста
    const truncateText = (text, maxLength = 150) => {
        if (!text) return '';
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    };

    // Обработчик отправки формы
    const handleSubmit = (e) => {
        e.preventDefault();
        saveNote();
    };

    return (
        <div className="App">
            {/* Навигация */}
            <nav className="navbar">
                <div className="container">
                    <div className="navbar-brand">
                        📝 Notes Application
                    </div>
                    <div className="nav-info">
                        Total notes: {notes.length}
                    </div>
                </div>
            </nav>

            <div className="container">
                {/* Сообщения об ошибках/успехе */}
                {error && (
                    <div className="error-message">
                        <strong>Error:</strong> {error}
                        <button
                            onClick={fetchNotes}
                            className="btn btn-primary ml-2"
                        >
                            Retry
                        </button>
                    </div>
                )}

                {message.text && (
                    <div className={`${message.type === 'success' ? 'success-message' : 'error-message'}`}>
                        {message.text}
                    </div>
                )}

                {/* Форма для создания/редактирования заметок */}
                <div id="note-form" className="form-container">
                    <h2 className="form-title">
                        {editId ? '✏️ Edit Note' : '➕ Create New Note'}
                    </h2>

                    <form onSubmit={handleSubmit}>
                        <div className="form-group">
                            <label className="form-label">
                                Title <span className="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                className="form-input"
                                placeholder="Enter note title"
                                value={title}
                                onChange={(e) => setTitle(e.target.value)}
                                minLength="3"
                                maxLength="255"
                                required
                            />
                            <small className="text-muted">
                                Minimum 3 characters, maximum 255 characters
                            </small>
                        </div>

                        <div className="form-group">
                            <label className="form-label">
                                Content <span className="text-danger">*</span>
                            </label>
                            <textarea
                                className="form-input form-textarea"
                                placeholder="Enter note content"
                                value={content}
                                onChange={(e) => setContent(e.target.value)}
                                rows="4"
                                required
                            />
                        </div>

                        <div className="form-actions">
                            <button
                                type="submit"
                                className="btn btn-primary"
                            >
                                {editId ? '💾 Update Note' : '✨ Create Note'}
                            </button>

                            {editId && (
                                <button
                                    type="button"
                                    className="btn btn-secondary"
                                    onClick={cancelEdit}
                                >
                                    ❌ Cancel
                                </button>
                            )}
                        </div>
                    </form>
                </div>

                {/* Список заметок */}
                <div className="notes-section">
                    <div className="section-header">
                        <h2>📋 My Notes ({notes.length})</h2>
                        <button
                            onClick={fetchNotes}
                            className="btn btn-secondary"
                            disabled={loading}
                        >
                            {loading ? '🔄 Loading...' : '🔄 Refresh'}
                        </button>
                    </div>

                    {loading ? (
                        <div className="loading">
                            <div className="spinner"></div>
                            <p>Loading notes...</p>
                        </div>
                    ) : notes.length === 0 ? (
                        <div className="empty-state">
                            <div className="empty-icon">📝</div>
                            <h3>No notes yet</h3>
                            <p className="text-muted">
                                Create your first note using the form above!
                            </p>
                        </div>
                    ) : (
                        <div className="notes-grid">
                            {notes.map(note => (
                                <div key={note.id} className="note-card">
                                    <div className="note-header">
                                        <h3 className="note-title">{note.title}</h3>
                                        <div className="note-actions">
                                            <button
                                                className="btn-edit"
                                                onClick={() => editNote(note)}
                                                title="Edit note"
                                            >
                                                ✏️ Edit
                                            </button>
                                            <button
                                                className="btn-delete"
                                                onClick={() => deleteNote(note.id)}
                                                title="Delete note"
                                            >
                                                🗑️ Delete
                                            </button>
                                        </div>
                                    </div>

                                    <div className="note-body">
                                        <p className="note-content">
                                            {truncateText(note.content)}
                                        </p>

                                        {note.content.length > 150 && (
                                            <button
                                                className="btn-read-more"
                                                onClick={() => {
                                                    // Простая реализация просмотра полного текста
                                                    alert(note.content);
                                                }}
                                            >
                                                Read more...
                                            </button>
                                        )}
                                    </div>

                                    <div className="note-footer">
                                        <div className="note-dates">
                                            <div className="note-date">
                                                <span className="date-label">Created:</span>
                                                <span className="date-value">
                                                    {formatDate(note.created_at || note.createdAt)}
                                                </span>
                                            </div>
                                            <div className="note-date">
                                                <span className="date-label">Updated:</span>
                                                <span className="date-value">
                                                    {formatDate(note.updated_at || note.updatedAt)}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="note-id">
                                            ID: {note.id}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* Футер */}
                <footer className="app-footer">
                    <div className="footer-content">
                        <p className="footer-text">
                            <strong>Notes Application</strong> &copy; {new Date().getFullYear()}
                        </p>
                        <p className="footer-info">
                            A simple note-taking application built with React and Symfony
                        </p>
                        <div className="footer-stats">
                            <span className="stat">
                                <strong>API:</strong> {window.location.origin}/api/notes
                            </span>
                            <span className="stat">
                                <strong>Status:</strong> {error ? '❌ Offline' : '✅ Online'}
                            </span>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    );
}

export default App;