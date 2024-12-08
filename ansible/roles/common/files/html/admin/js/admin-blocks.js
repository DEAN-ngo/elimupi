Vue.component( 'admin-blocks', {
    props:['visible'],
    template: `<div class='admin-blocks'><slot :visible="visible"/></div>`
})