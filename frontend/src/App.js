import React, { useState, useEffect } from 'react';

function App() {
    const URL_API = 'http://localhost:8080';
    const [notes, setNotes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const [title, setTitle] = useState('');
    const [content, setContent] = useState('');
    const [editId, setEditId] = useState(null);

    // Загрузка заметок
    const loadNotes = async () => {
        try {
            setLoading(true);
            const response = await fetch(URL_API + '/api/notes/');
            const data = await response.json();
            setNotes(data);
            setError(null);
        } catch (err) {
            setError('Ошибка загрузки заметок');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    // Загрузить заметки при запуске
    useEffect(() => {
        loadNotes();
    }, []);

    // Сохранить/обновить заметку
    const saveNote = async () => {
        if (!title.trim() || !content.trim()) {
            alert('Заполните заголовок и содержание');
            return;
        }

        const noteData = { title, content };

        try {
            if (editId) {
                // Обновление
                await fetch(URL_API + `/api/notes/${editId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(noteData)
                });
            } else {
                // Создание
                await fetch(URL_API + '/api/notes/', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(noteData)
                });
            }

            // Очистить форму и перезагрузить список
            setTitle('');
            setContent('');
            setEditId(null);
            loadNotes();

        } catch (err) {
            alert('Ошибка сохранения');
            console.error(err);
        }
    };

    // Удалить заметку
    const deleteNote = async (id) => {
        if (!window.confirm('Удалить заметку?')) return;

        try {
            await fetch(URL_API + `/api/notes/${id}`, { method: 'DELETE' });
            loadNotes();
        } catch (err) {
            alert('Ошибка удаления');
            console.error(err);
        }
    };

    // Редактировать заметку
    const editNote = (note) => {
        setTitle(note.title);
        setContent(note.content);
        setEditId(note.id);
    };

    // Отмена редактирования
    const cancelEdit = () => {
        setTitle('');
        setContent('');
        setEditId(null);
    };

    return (
        <div className="container">
            <h1>📝 Заметки</h1>

            {/* Форма */}
            <div style={{ background: 'white', padding: '20px', borderRadius: '5px', marginBottom: '20px' }}>
                <h3>{editId ? 'Редактировать заметку' : 'Новая заметка'}</h3>

                <div>
                    <input
                        type="text"
                        placeholder="Заголовок"
                        value={title}
                        onChange={(e) => setTitle(e.target.value)}
                    />
                </div>

                <div>
                    <textarea
                        placeholder="Содержание"
                        value={content}
                        onChange={(e) => setContent(e.target.value)}
                        rows="4"
                    />
                </div>

                <div>
                    <button
                        className="btn-primary"
                        onClick={saveNote}
                    >
                        {editId ? 'Обновить' : 'Создать'}
                    </button>

                    {editId && (
                        <button
                            className="btn-secondary"
                            onClick={cancelEdit}
                        >
                            Отмена
                        </button>
                    )}
                </div>
            </div>

            {/* Статус загрузки */}
            {loading && <div>Загрузка...</div>}

            {/* Ошибка */}
            {error && (
                <div style={{ background: '#ffdddd', padding: '10px', borderRadius: '4px', margin: '10px 0' }}>
                    {error}
                    <button onClick={loadNotes} style={{ marginLeft: '10px' }}>Повторить</button>
                </div>
            )}

            {/* Список заметок */}
            <div>
                <h3>Все заметки ({notes.length})</h3>

                {notes.length === 0 && !loading ? (
                    <div style={{ textAlign: 'center', padding: '40px', color: '#666' }}>
                        Нет заметок.
                    </div>
                ) : (
                    notes.map(note => (
                        <div key={note.id} className="note">
                            <h4>{note.title}</h4>
                            <p>{note.content}</p>

                            <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: '10px' }}>
                                <small style={{ color: '#666' }}>
                                    Создано: {new Date(note.created_at).toLocaleDateString()}
                                </small>

                                <div>
                                    <button
                                        className="btn-secondary"
                                        onClick={() => editNote(note)}
                                        style={{ marginRight: '10px' }}
                                    >
                                        ✏️ Изменить
                                    </button>
                                    <button
                                        className="btn-danger"
                                        onClick={() => deleteNote(note.id)}
                                    >
                                        🗑️ Удалить
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))
                )}
            </div>
        </div>
    );
}

export default App;