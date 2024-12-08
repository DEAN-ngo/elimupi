Vue.component( 'installed-versions', {
    props: ['cmd'],
    data(){
        return {
            pi: '-',
            moodle: '-',
            moosh: '-',
            kolibri: '-',
            fdroid: '-',
            kiwix: '-',
            php: '-',
            python2: '-',
            python3: '-'
        }
    },
    methods: {
        start(){
            this.init()
            var progress = this.$el.querySelector('progress')
            progress.removeAttribute('value')
            this.$props.cmd('getInstalledVersions')
            .catch( this.fail )
            .then( ( json ) => {
                if( typeof json != 'undefined'){
                    this.pi = json['msg']['pi']
                    this.moodle = json['msg']['moodle']
                    this.moosh = json['msg']['moosh']
                    this.kolibri = json['msg']['kolibri']
                    this.fdroid = json['msg']['fdroid']
                    this.kiwix = json['msg']['kiwix']
                    this.php = json['msg']['php']
                    this.python2 = json['msg']['python2']
                    this.python3 = json['msg']['python3']
                }
                this.end()
            })
        },
        init(){
            if( ! this.$el.querySelector('progress') ){
                var p = document.createElement('progress')
                this.$el.prepend(p)
            }
        },
        end(){
            var progress = this.$el.querySelector('progress')
            progress.remove()
        },
        fail(){
            var progress = this.$el.querySelector('progress')
            progress.value = 0
        }
    },
    template: `<div>
                <progress max='100' value='0'/>
                <div class='installed-versions'>
                    <div>
                        <div>- Pi : </div><div>{{ pi }}</div>
                    </div>
                    <div>
                        <div>- Moodle : </div><div>{{ moodle }}</div>
                    </div>
                    <div>
                        <div>- Moosh : </div><div>{{ moosh }}</div>
                    </div>
                    <div>
                        <div>- Kolibri : </div><div>{{ kolibri }}</div>
                    </div>
                    <div>
                        <div>- Fdroid : </div><div>{{ fdroid }}</div>
                    </div>
                    <div>
                        <div>- Kiwix : </div><div>{{ kiwix }}</div>
                    </div>
                    <div>
                        <div>- PHP : </div><div>{{ php }}</div>
                    </div>
                    <div>
                        <div>- Python2 : </div><div>{{ python2 }}</div>
                    </div>
                    <div>
                        <div>- Python3 : </div><div>{{ python3 }}</div>
                    </div>
                </div>
            </div>`
})