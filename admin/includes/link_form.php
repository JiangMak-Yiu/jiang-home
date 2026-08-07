<?php
/**
 * 友链表单片段(添加/编辑共用)
 * 需要变量: $edit(可选,当前编辑的友链)
 */
$pdo = db();
$v = $edit ?: [];
$fv = fn(string $k, string $d = '') => e($v[$k] ?? $d);
?>
<div class="form-grid">
    <label class="field">
        <span>名称 *</span>
        <input type="text" name="name" value="<?= $fv('name') ?>" required maxlength="60">
    </label>
    <label class="field">
        <span>链接 URL *</span>
        <input type="url" name="url" value="<?= $fv('url') ?>" required maxlength="300" placeholder="https://example.com">
    </label>
    <label class="field">
        <span>图标 URL(头像)</span>
        <input type="url" name="icon" value="<?= $fv('icon') ?>" maxlength="300" placeholder="https://example.com/icon.png(可留空)">
    </label>
    <label class="field">
        <span>封面图 URL(推荐网站/项目显示)</span>
        <input type="url" name="cover" value="<?= $fv('cover') ?>" maxlength="300" placeholder="封面大图,可留空">
    </label>
    <label class="field">
        <span>简介</span>
        <input type="text" name="description" value="<?= $fv('description') ?>" maxlength="120">
    </label>
    <label class="field">
        <span>分类</span>
        <input type="text" name="category" value="<?= $fv('category', '默认') ?>" maxlength="30" list="cat-list" placeholder="输入新分类或选择已有">
        <datalist id="cat-list">
            <?php foreach ($pdo->query('SELECT DISTINCT category FROM links ORDER BY category') as $r): ?>
            <option value="<?= e($r['category']) ?>">
            <?php endforeach; ?>
        </datalist>
    </label>
    <label class="field">
        <span>排序(数字越小越靠前)</span>
        <input type="number" name="sort" value="<?= $fv('sort', '0') ?>" min="0" max="9999">
    </label>
</div>
