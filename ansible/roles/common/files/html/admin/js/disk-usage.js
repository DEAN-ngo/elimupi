Vue.component('disk-usage', {
    props: ['title-txt', 'total-used-txt', 'mount-txt', 'eject-txt', 'cmd', 'is-admin'],

    data(){
        return {
            diskUsage: [{result:{}}]
        }
    },

    methods:{
        ejectMedia: function( event ){
            var which = event.target.getAttribute( 'rel' )

            this.$props.cmd( 'ejectMedia', { which: which } )
            .then( this.getDiskUsage )
        },

        mountFS: function(event){
            var which = event.target.getAttribute( 'rel' )

            this.$props.cmd( 'mountDisk', { which: which } )
            .then( this.getDiskUsage )
        },

        kBytesToString: function( str ){
            var bytes = parseInt( str ),
                lang = document.querySelector( 'html' ).getAttribute( 'lang' ) || 'en-GB';
        
            if( bytes > Math.pow( 10, 9)){
                bytes = bytes / Math.pow( 10, 9)
                return Number( Math.round( bytes * 100 ) / 100).toLocaleString( lang, { minimumFractionDigits: 2 }) + ' TB'
            }
            else if( bytes > Math.pow( 10, 6)){
                bytes = bytes / Math.pow( 10, 6)
                return Number( Math.round( bytes * 100 ) / 100).toLocaleString( lang, { minimumFractionDigits: 2 })+ ' GB'
            }
            else if( bytes > Math.pow( 10, 3)){
                bytes = bytes / Math.pow( 10, 3)
                return Number(Math.round( bytes * 100) / 100).toLocaleString( lang, { minimumFractionDigits: 2 }) + ' MB'
            }
        
            return bytes + ' KB'
        },
        
        showResultDiskUsage: function( json ){
        
            var mounted = 0;

            if( typeof json == 'undefined') return 
                    
            json.forEach( ( disk ) => {
                if( disk.result.unmounted == false )
                    mounted++
                disk.result.Size = ! disk.result[ '1K-blocks' ]? disk.result['blocks'] : this.kBytesToString( disk.result[ '1K-blocks' ])
                disk.result.Used = this.kBytesToString( disk.result[ 'Used' ])
            })
    
            this.$emit('has-secondary-disk', mounted > 2)
        
            this.diskUsage = json
        },

        getDiskUsage: function(){
            this.$props.cmd( 'diskUsage' )
            .then( this.showResultDiskUsage )
        }
    },

    mounted(){
        this.getDiskUsage()
    },

    template: `<div>
                    <div class='scroll disks'>

                    <p></p>

                    <h1 id='total-internal-used'>{{ diskUsage[0].result.Used }}</h1>
                    <span>{{ totalUsedTxt }}&nbsp;<span id='total-internal-available'>{{ diskUsage[0].result.Size}}</span></span>
                    <hr>

                    <p class='title'>
                        {{ titleTxt }}
                    </p>

                    <br>

                    <div class='internal'>
                        <div class='icon'><img src='img/disk.svg'></div>
                        <div class='disk-control'>
                            <div class='disk-usage'>
                                <div class='used' v-bind:style="{ width: diskUsage[0].result[ 'Use%' ]}">
                                    <p>{{ diskUsage[0].result[ 'Use%' ]}}</p>
                                </div>
                            </div>

                            <div class='total'>
                                <span>{{ diskUsage[ 0 ].result[ 'Size' ]}}</span>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div id='removable-disks'>
                        <div v-for='(disk, index) in diskUsage' v-if='index!=0'>
                            <p>{{ disk.mnt }}</p>
                            <p>{{ disk.result.Size }}</p>
                            <div class='icon'><img src='img/disk.svg'></div>
                            <div class='disk-control'>
                                <div v-bind:class="{'disk-usage': true, grayed: disk.result.unmounted}">
                                    <div class='used' v-bind:style="{ width: disk.result[ 'Use%' ]}">
                                        <p>{{ disk.result['Use%']}}</p>
                                    </div>
                                </div>

                                <div v-if="disk.result.unmounted" class='eject'>
                                    <button v-bind:disabled="! isAdmin" v-on:click="mountFS" v-bind:rel="disk.mnt">{{ mountTxt }}</button>
                                </div>
                                <div v-else class='eject'>
                                    <button v-bind:disabled="! isAdmin" v-on:click="ejectMedia" v-bind:rel="disk.mnt">{{ ejectTxt }}</button>
                                </div>  
                            </div>
                        </div>
                    </div>
                </div>
            </div>`
})