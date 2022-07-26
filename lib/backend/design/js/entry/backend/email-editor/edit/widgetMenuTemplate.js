export default function () {
    let tr = emailEditor.data.tr;
    return `
    <span class="menu-widget">
        <span class="email-widget-edit-box" title="${tr.EDIT_WIDGET}"></span>
        <span class="email-widget-handle" title="${tr.MOVE_BLOCK}"></span>
        <span class="email-widget-remove-box" title="${tr.REMOVE_WIDGET}"></span>
    </span>
    `;
}