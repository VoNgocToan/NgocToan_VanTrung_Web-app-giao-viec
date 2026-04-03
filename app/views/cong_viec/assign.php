<div class="section-header">
    <div>
        <div class="section-title">Phân công công việc</div>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/show', ['id' => $task['id']])) ?>">Quay lại</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="post" action="<?= e(route_url('cong_viec/saveAssignment', ['id' => $task['id']])) ?>">
            <div class="row g-3">
                <div class="col-12 col-lg-7">
                    <label class="form-label fw-semibold">Thành viên phụ trách</label>

                    <?php
                    $selectedNames = [];
                    foreach ($members as $member) {
                        if (in_array((int) $member['user_id'], $selectedAssigneeIds ?? [], true)) {
                            $selectedNames[] = $member['name'];
                        }
                    }
                    ?>

                    <div class="assignee-dropdown" data-assignee-dropdown>
                        <button type="button" class="assignee-toggle" data-assignee-toggle aria-expanded="false">
                            <span class="assignee-toggle-label" data-assignee-label>
                                <?= !empty($selectedNames) ? e(implode(', ', $selectedNames)) : 'Chọn thành viên phụ trách' ?>
                            </span>
                            <span class="assignee-toggle-icon">▾</span>
                        </button>

                        <div class="assignee-menu" data-assignee-menu hidden>
                            <div class="assignee-menu-head">Danh sách thành viên</div>
                            <div class="assignee-option-list">
                                <?php foreach ($members as $member): ?>
                                    <?php $checked = in_array((int) $member['user_id'], $selectedAssigneeIds ?? [], true); ?>
                                    <label class="assignee-option">
                                        <input type="checkbox" name="assignee_ids[]" value="<?= e((string) $member['user_id']) ?>" <?= $checked ? 'checked' : '' ?>>
                                        <span class="assignee-option-text">
                                            <strong><?= e($member['name']) ?></strong>
                                            <small><?= e(role_label($member['role'])) ?></small>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="assignee-selected" data-assignee-selected>
                        <?php foreach ($members as $member): ?>
                            <?php $checked = in_array((int) $member['user_id'], $selectedAssigneeIds ?? [], true); ?>
                            <span class="assignee-pill <?= $checked ? '' : 'd-none' ?>" data-assignee-pill="<?= e((string) $member['user_id']) ?>">
                                <?= e($member['name']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-2">
                    <label class="form-label fw-semibold">Ngày giao việc</label>
                    <input type="date" class="form-control" name="start_date" value="<?= e($task['start_date'] ?? date('Y-m-d')) ?>">
                </div>

                <div class="col-sm-6 col-lg-3">
                    <label class="form-label fw-semibold">Hạn chót thực hiện</label>
                    <input type="date" class="form-control" name="deadline" value="<?= e($task['deadline']) ?>">
                </div>
            </div>

            <div class="mt-4 d-flex gap-2 flex-wrap">
                <button class="btn btn-primary">Lưu phân công</button>
                <a class="btn btn-outline-secondary" href="<?= e(route_url('cong_viec/show', ['id' => $task['id']])) ?>">Hủy</a>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const dropdown = document.querySelector('[data-assignee-dropdown]');
    if (!dropdown) return;

    const toggle = dropdown.querySelector('[data-assignee-toggle]');
    const menu = dropdown.querySelector('[data-assignee-menu]');
    const label = dropdown.querySelector('[data-assignee-label]');
    const selectedWrap = document.querySelector('[data-assignee-selected]');
    const inputs = dropdown.querySelectorAll('input[type="checkbox"]');

    const updateSelected = () => {
        const checked = Array.from(inputs).filter(input => input.checked);
        const names = checked.map(input => {
            const text = input.closest('.assignee-option').querySelector('strong');
            return text ? text.textContent.trim() : '';
        }).filter(Boolean);

        label.textContent = names.length ? names.join(', ') : 'Chọn thành viên phụ trách';

        if (selectedWrap) {
            selectedWrap.querySelectorAll('[data-assignee-pill]').forEach(pill => pill.classList.add('d-none'));
            checked.forEach(input => {
                const pill = selectedWrap.querySelector(`[data-assignee-pill="${input.value}"]`);
                if (pill) pill.classList.remove('d-none');
            });
        }
    };

    toggle.addEventListener('click', function () {
        const isOpen = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        menu.hidden = isOpen;
        dropdown.classList.toggle('is-open', !isOpen);
    });

    document.addEventListener('click', function (event) {
        if (!dropdown.contains(event.target)) {
            toggle.setAttribute('aria-expanded', 'false');
            menu.hidden = true;
            dropdown.classList.remove('is-open');
        }
    });

    inputs.forEach(input => input.addEventListener('change', updateSelected));
    updateSelected();
})();
</script>
