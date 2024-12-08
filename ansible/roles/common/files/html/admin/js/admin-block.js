Vue.component( 'admin-block', {
    props: ['title','is-icon','start', 'src'],
    methods: {
        toggle(){
            if( this.$parent.visible == this.$props.title )
                this.$parent.visible = ''
            else{
                if( this.$children[0] && typeof this.$children[0][this.start] == 'function')
                    this.$children[0][this.start]()
                this.$parent.visible = this.$props.title
            }
        },
    },
    template: `<div v-bind:class="{'admin-block':true, expanded: $parent.visible == title, 'has-icon': $props.isIcon}">
                <div v-if='! $props.isIcon'>
                    <h3 class='pointer' v-on:click.stop='toggle'>{{ title }}</h3>
                </div>
                <div v-else>
                    <img :src='$props.src' v-on:click.stop='toggle' :title='$props.title' class='pointer'/>
                </div>
                <div v-bind:class="{ 'hide': $parent.visible != title }"><slot/></div>
            </div>`
})