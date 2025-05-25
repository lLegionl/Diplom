<?php require '../views/layouts/header.php'; ?>

<div class="card">
    <h2>Создать новый документ</h2>
    <form method="POST" action="/documents/create">
        <div class="form-group">
            <label>Тип документа</label>
            <select name="type" required>
                <option value="order">Приказ</option>
                <option value="contract">Договор</option>
                <option value="application">Заявка</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Категория</label>
            <select name="category" required>
                <option value="incoming">Входящие</option>
                <option value="outgoing">Исходящие</option>
                <option value="internal">Внутренние</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Название</label>
            <input type="text" name="title" required>
        </div>
        
        <div class="form-group">
            <label>Описание</label>
            <textarea name="description" rows="4"></textarea>
        </div>
        
        <button type="submit" class="btn">Создать</button>
    </form>
</div>

<?php require '../views/layouts/footer.php'; ?>