Vue.component( 'sync-packages', {

    props: [ 'cmd', 'has-secondary-disk', 'elimugo-txt', 
            'second-disk-txt', 'online-txt', 'looking-for-source-txt', 
            'warning-txt', 'installed-txt', 'newTxt', 
            'refresh', 'updates-txt', 'copied-txt', 
            'verified-txt', 'installed-txt', 'button-txt', 
            'lang'],

    data(){
        return {
            busy: false,
            outputSyncPackages: null,
            foundSource: false,
            hasSecondaryDisk: false,
            statusListPackagesForCopy: '',
            listPackagesForCopy: null,
            doingSyncPackages: false,
            packageSpinnerClasses: [],
            install: [],
            verify: [],
            hasElimuGo: false,
            syncPackagesState: ''
        }
    },

    mounted(){
        this.setInitialObjectSyncPackages(true)
        this.doSyncRepo()
    },

    methods:{
        
        doSyncRepo: function(){

            if( this.busy ) return

            this.foundSource = false
            this.hasSecondaryDisk = false
            this.hasElimuGo = false
            this.statusListPackagesForCopy = ''
            this.listPackagesForCopy = null
            this.setInitialObjectSyncPackages(true)

            Vue.nextTick( () => {
                this.$props.cmd( 'getPackagesAvailableSources' )
                .then( ( json ) => {
                    if( typeof json != 'undefined'){
                        this.loading = false
                        if( json.elimuGo )
                            this.hasElimuGo = true
                        if( json.secondDisk )
                            this.hasSecondaryDisk = true
                        this.foundSource = true
                        this.outputSyncPackages.installed = json.numberOfPackages
    
                        if( this.doingSyncPackages ){
                            if( this.syncPackagesState == 'new' )
                                this.packagesSpinner( 'on', '.newPackages' )
                            else if( this.syncPackagesState == 'copy')
                                this.packagesSpinner( 'on', '.copyPackages' )
                            else if( this.syncPackagesState == 'verify')
                                this.packagesSpinner( 'on', '.verifyPackages' )
                        }
                    }
                })
            })
        },

        setInitialObjectSyncPackages: function( bool ){
            if( ! this.outputSyncPackages || bool )
                this.outputSyncPackages = {
                    status: '',
                    installed: this.outputSyncPackages ? this.outputSyncPackages.installed : 0,
                    new: [],
                    updates: [],
                    copied: 0,
                    verified: 0
                }
        },

        doSyncPackagesStatus: function(){

            if( this.doingSyncPackages ) return

            this.busy = true

            this.doSyncPackagesStatus = true
            this.setInitialObjectSyncPackages( true )
            Vue.nextTick( () => {
                this.packagesSpinner( 'on', '.newPackages' )
                this.syncPackagesState = 'new'

                this.outputSyncPackages.status = msgFetchingPackagesInfo
                this.doSyncPackagesCountNew()
                .then( ( json ) => {

                    if( typeof json != 'undefined'){
                        this.packagesSpinner( 'off', '.newPackages' )

                        this.doSyncPackagesStatus = false
    
                        if( json.msg.match( /^Error/ ))
                            this.outputSyncPackages.status = json.msg
                        else {
                            if( json.newPackages ){
                                json.newPackages.forEach( ( package ) => {
                                    this.outputSyncPackages.new.push( package )
                                })
                                this.outputSyncPackages.status = ''
                            }
                            if( json.updatePackages ){
                                json.updatePackages.forEach( ( package ) => {
                                    this.outputSyncPackages.updates.push( package )
                                })
                            }
                        }
                    }
                })

            } )
        },

        packagesSpinner: function( phase, cls ){
            var sel = this.$el.querySelector( cls + ' .spinner' ),
                index = 0,
                char = [ '|', '/', '-', "\\" ];
            
            if( phase == 'off' ){
                if( this.packagesSpinnerTimer )
                    clearInterval( this.packagesSpinnerTimer )
                this.packagesSpinnerTimer = null
                sel.classList.add( 'hide' )
            }
            else{
                sel.classList.remove( 'hide' )
                sel.innerHTML = char[ index++ % char.length ]
                this.packagesSpinnerTimer = setInterval( () => {
                    sel.innerHTML = char[ index++ % char.length ]
                }, 125 )
            }

            if( this.packageSpinnerClasses.indexOf( cls ) == -1)
                this.packageSpinnerClasses.push( cls )
        },

        packageSpinnersOff: function(){
            this.packageSpinnerClasses.forEach( ( cls ) => {
                this.packagesSpinner( 'off', cls )
            })
        },

        doSyncPackagesCountNew: function(){
            return new Promise( ( resolve ) => {
                this.$props.cmd( 'newPackages', {
                    elimuGo: this.elimuGo ? 'yes' : 'no',
                    lang: this.lang 
                })
                .catch( ( error ) => {
                    this.outputSyncPackages.status = error
                    this.packagesSpinner( 'off', '.newPackages' )
                    this.syncPackagesState = ''
                })
                .then( ( json ) => {
                    resolve( json )
                } )
            })
        },

        doSyncPackagesFetch: function(){

            if( this.doingSyncPackages ) return

            this.doingSyncPackages = true

            var selected = document.querySelectorAll( '#selectedPackages input[type=checkbox]:checked' ),
                selectedIds = [];

            if( selected ){
                selected.forEach( ( cb ) => { 
                    selectedIds.push( cb.getAttribute( 'name' ))
                } )

                var newPackages = this.outputSyncPackages.new.filter( ( p ) => {
                    return selectedIds.indexOf( p.uniqueId ) > -1
                })

                var updates = this.outputSyncPackages.updates.filter( ( p ) => {
                    return selectedIds.indexOf( p.uniqueId ) > -1
                })

                this.install = newPackages.concat( updates )

                if( this.install.length == 0) return

                this.verify = this.install.concat()

                this.syncPackagesState = 'copy'
                this.packagesSpinner( 'on', '.copyPackages' )
                this.outputSyncPackages.new = 0
                this.outputSyncPackages.updates = 0

                this.copyPackages( this.install )
                .then( () => {
                    this.syncPackagesState = 'verify'
                    this.packagesSpinner( 'off', '.copyPackages' )
                    this.packagesSpinner( 'on', '.verifyPackages' )
                    return this.verifyPackages( this.verify )
                })
                .then( () => {
                    this.syncPackagesState = ''
                    this.packagesSpinner( 'off', '.verifyPackages' )
                    this.packagesSpinner( 'on', '.installPackages' )
                    this.doingSyncPackages = false
                })
                .then( () => {
                    return this.installPackages(  newPackages.concat( updates ) )
                })
                .then( () => { 
                    this.outputSyncPackages.status = msgDoneUpdating
                    this.packagesSpinner( 'off', '.installPackages' )
                    this.busy = false
                })
            }

        },

        copyPackage: async function( json ){
            this.showResultCopy( json )
            return this.$props.cmd( 'copyPackages', { 
                elimuGo: this.hasElimuGo? 'yes' : 'no', 
                packages: [ this.install.shift() ] 
            })
        },

        verifyPackage: function( json ){
            this.showResultVerify( json )
            return this.$props.cmd( 'verifyPackages', { packages: [ this.verify.shift() ] } )
        },

        installPackages: function( packages ){
            this.outputSyncPackages.status = msgInstallingPackages
            return this.$props.cmd( 'install', { packages: packages } )
        },

        copyPackages: function( install ){
            return new Promise( ( resolve ) => {
                install.reduce( ( previous ) => 
                    previous.then( this.copyPackage )
                , Promise.resolve( { msg: msgCopyingPackages }))
                .then( this.showResultCopy )
                .then( resolve )
            })
        },

        verifyPackages: function( install ){
            return new Promise( ( resolve ) => {
                install.reduce( ( previous ) => 
                    previous.then( this.verifyPackage )
                , Promise.resolve( { msg: msgVeryfyingPackages } ))
                .then( this.showResultVerify )
                .then( resolve )
            })
        },

        showResultCopy: function( json ){
            if( json.msg )
                this.outputSyncPackages.status = json.msg
            else
                this.outputSyncPackages.copied += json.copied
            return Promise.resolve()
        },
    
        showResultVerify: function( json ){
            if( json.msg )
                this.outputSyncPackages.status = json.msg
            else{
                this.outputSyncPackages.verified += json.verified
            }
            return Promise.resolve()
        }
    },
  
    template: `<div>
                    <progress v-if='! foundSource'/>
                    <div class='syncPackagesProgress'>
                        <div v-if='foundSource'>
                            <h2 v-if='hasElimuGo'>{{ elimugoTxt}} <a v-if='! busy' class='pointer' v-on:click='doSyncRepo'>&#128472;</a></h2>
                            <h2 v-else-if='hasSecondaryDisk'>{{ secondDiskTxt}} <a v-if='! busy' class='pointer' v-on:click='doSyncRepo'>&#128472;</a></h2>
                            <h2 v-else>{{ onlineTxt }} <a v-if='! busy' class='pointer' v-on:click='doSyncRepo'>&#128472;</a></h2>
                        </div>
                        <div v-else>
                            <h2>{{ lookingForSourceTxt }}</h2>
                        </div>
                        <div v-if='! hasSecondaryDisk && foundSource'>
                            <div class='popup'>
                                <div class='inner'>
                                {{ warningTxt }}
                                </div>
                            </div>
                        </div>
                        <div class='status' v-if='outputSyncPackages'>
                            <div>
                                <span>{{ installedTxt }}&nbsp;</span>
                                <span>{{ outputSyncPackages.installed }}</span>
                            </div>
                            <div class='newPackages'>
                                <span>{{ newTxt }}&nbsp;</span>
                                <span>{{ outputSyncPackages.new.length }}</span> 
                                <span class='spinner hide'></span>
                                <span class='spinner'><a class='pointer' v-if='foundSource' v-on:click='doSyncPackagesStatus'>&#128472;</a></span>
                            </div>
                            <div>
                                <span>{{ updatesTxt }}&nbsp;</span>
                                <span>{{ outputSyncPackages.updates.length }}</span>
                            </div>
                            <div class='copyPackages'>
                                <span>{{ copiedTxt }}&nbsp;</span>
                                <span>{{ outputSyncPackages.copied }}</span> 
                                <span class='spinner hide'></span>
                            </div>
                            <div class='verifyPackages'>
                                <span>{{ verifiedTxt }}&nbsp;</span>
                                <span>{{ outputSyncPackages.verified }}</span> 
                                <span class='spinner hide'></span>
                            </div>
                            <div class='installPackages'>
                                <span>{{ installedTxt }}&nbsp;</span>
                                <span class='spinner hide'></span>
                            </div>
                            <div>
                                <span><?php;?></span>
                                <span class='status'>{{ outputSyncPackages.status }}</span>
                            </div>
                        </div>
                        <div v-else class='status'></div>
                        <div v-if='outputSyncPackages && ( outputSyncPackages.new.length > 0 || outputSyncPackages.updates.length > 0 )' id='selectedPackages'>
                            <div v-for='package in outputSyncPackages.updates'>
                                <div>
                                    <label class="switch">
                                        <input type='checkbox' v-bind:name="package.uniqueId" checked />
                                        <span class="slider round"></span>
                                    </label>
                                    {{ package.description }}
                                </div>
                            </div>
                            <div v-for='package in outputSyncPackages.new'>
                                <div>
                                    <label class="switch">
                                        <input type='checkbox' v-bind:name="package.uniqueId" checked />
                                        <span class="slider round"></span>
                                    </label>                                    
                                    {{ package.description }}
                                </div>
                            </div>
                            <br>
                        </div>
                        <div v-if='outputSyncPackages && ( outputSyncPackages.new.length > 0 || outputSyncPackages.updates.length > 0 )'>
                            <a v-on:click='doSyncPackagesFetch'>{{ buttonTxt }}</a>
                        </div>
                    </div>
                </div>`
})