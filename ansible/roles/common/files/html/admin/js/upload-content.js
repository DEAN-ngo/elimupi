Vue.component( 'upload-content', {
    props: ['max-upload','share-txt','title-txt','update-txt','update-id-txt','submit-txt','nothing-found','local-package-txt','cmd'],
    data(){
        return {
            result: '',
            notFound: false,
            isUpdate: false
        }
    },
    methods: {
        updateVisible( event ){
            this.isUpdate = event.target.checked
            if( this.isUpdate ){
                this.$props.cmd( 'getLocalPackagesIds' )
                .then( ( json ) => {
                    if( typeof json !== 'undefined'){
                        var dl = this.$el.querySelector('datalist')
                        json.packages.forEach(element => {
                            var e = document.createElement('option'),
                                prop = Object.getOwnPropertyNames(element)[0]
                            e.innerHTML = prop
                            e.value = element[prop]
                            dl.appendChild(e)
                        });
                        
                        if(json.packages.length == 0)
                            this.notFound = true
                    }
                })
            }
        },
        upload(){
            this.init()
            this.start()

            var f = this.$el.querySelector('form'),
                params = {}

            params['zip'] = f.querySelector('input[name=zip]').files[0]
            params['public'] = f.querySelector('input[name=public]').checked ? 'on' : ''
            params['packageId'] = ''
            var pack = f.querySelector('input[name=packageId]')
            if( pack )
                params['packageId'] = pack.value
            params['title'] = f.querySelector('input[name=title]').value
            
            this.$props.cmd( 'uploadZIP',  params )
            .catch( this.fail )
            .then( ( json ) => {
                if( typeof json != 'undefined' )
                    this.result = json.msg
                this.end()
            })
        },
        start(){
            this.$el.querySelector('button').classList.add('hide')
            var progress = this.$el.querySelector('progress')
            progress.removeAttribute('value')
        },
        end(){
            this.$el.querySelector('button').classList.remove('hide')
            var progress = this.$el.querySelector('progress')
            progress.remove()
        },
        init(){
            if( ! this.$el.querySelector('progress') ){
                var p = document.createElement('progress')
                this.$el.prepend(p)
            }
        },
        fail(){
            this.$el.querySelector('button').classList.remove('hide')
            var progress = this.$el.querySelector('progress')
            progress.value = 0
        }
    },
    template: `<div>
                <span>{{ result }}</span>
                <form class='columns' v-on:submit.prevent="upload">
                    <div>
                        <div>{{ shareTxt }}</div>
                        <label class='switch'>
                            <input type='checkbox' name='public'/>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div>
                        <div>{{ updateTxt }}</div>
                        <label class='switch'>
                            <input type='checkbox' name='public' v-on:change='updateVisible'/>
                            <span class="slider round"></span>
                        </label>
                        </div>
                    <div>
                        <div>{{ titleTxt }}</div>
                        <label>
                            <input type='text' name='title'></input>
                        </label>
                    </div>
                    <div v-if='isUpdate'>
                        <div>{{ localPackageTxt }}</div>
                        <input list='packIds' type="text" name='packageId' v-bind:placeholder='updateIdTxt'/> 
                        <label></label>
                        <datalist id="packIds"></datalist>
                        <span v-if='notFound'>{{ nothingFound }}</span>
                    </div>
                    <div>
                        <label>
                            <input name='zip' type='file'></input>
                            {{ maxUpload }}
                        </label>
                    </div>
                    <button>{{ submitTxt }}</button>
                </form>
            </div>`
})