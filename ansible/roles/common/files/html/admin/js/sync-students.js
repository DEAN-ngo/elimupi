Vue.component('sync-students', {
    props: ['cmd', 'is-valid-password', 'title-txt', 'remove-txt', 'removed-txt', 'initial-password-txt', 'submit-txt', 'start-adding', 'added-txt', 'nothing-added'],
    data(){
        return {
            outputSync: null,
            validPassword: false,
            additions: [],
            studentInitialPassword: ''
        }
    },
    methods:{
        keyUp(){

            return  this.checkPassword()
        },

        checkPassword(){
            var text = this.$el.querySelector( 'input[name=password]' ).value
            this.validPassword = this.$props.isValidPassword(text)
        },
        doUploadStudentsFile: function(){

            if( this.validPassword ){
                var fileEl = this.$el.querySelector( 'input[type=file]' ),
                    password = this.$el.querySelector( 'input[name=password]' ).value,
                    remove = this.$el.querySelector( 'input[name=remove]' ).checked;

                var reader = new FileReader();

                reader.onload = ( event ) => {
                    this.doSync( event.target.result, password, remove )
                }

                reader.readAsText( fileEl.files[ 0 ] )
            }
        },
        
        setItialObjectSync: function(){
            this.outputSync = {
                removedStatus: '',
                totalRemoved: 0,
                addedErrors: [],
                totalAdded: 0
            }
        },


        showOutputSyncAddition: function( json ){
            if( json && json.status && json.status == 'ok' )
                this.outputSync.totalAdded++
            else
                this.outputSync.addedErrors.push( json.msg )
            return Promise.resolve() 
        },

        showOutputSyncRemoval: function( json ){
            this.totalRemoved++
            return Promise.resolve()
        },

        addStudent: function( json ){
            this.showOutputSyncAddition( json )
            return this.$props.cmd( 'addUser', { userName: this.additions.shift(), newPassword: this.studentInitialPassword, type: 'students' } )
        },


        removeStudent: function( json ){
            showOutputSyncRemoval( json )
            return this.$props( 'deleteUser', { userName: this.removals.shift() } )
        },

        addStudents: function( additions, password ){
                
            this.studentInitialPassword = password
            this.additions = additions

            return new Promise( ( resolve ) => {
                additions.reduce( ( previous ) =>
                    previous.then( this.addStudent )
                , Promise.resolve( { msg: this.startAdding } ))
                .then( this.showOutputSyncAddition )
                .then( () => {
                    if( this.outputSync.totalAdded == 0 )
                        this.showOutputSyncAddition( { msg:  this.nothingAdded } )
                    this.loading = false
                })
                .then( resolve )
            })
        },

        removeStudents: function( removals ){
            this.removals = removals

            return new Promise( ( resolve ) => {
                removals.reduce( ( previous ) =>
                    previous.then( this.removeStudent )
                , Promise.resolve( { msg: this.startAdding } ))
                .then( this.showOutputSyncRemoval )
                .then( () => {
                    if( this.outputSync.totalAdded == 0 )
                        this.showOutputSyncRemoval( { msg:  this.nothingAdded } )
                    this.loading = false
                })
                .then( resolve )
            })
        },

        doSync: function( text, password, remove ){
            if( text ){

                var currentStudents = [];

                this.setItialObjectSync()
                Vue.nextTick( () => {
                    this.$props.cmd( 'listUsers', { types: 'students' } )
                    .then( ( json ) => {
                        
                        if( typeof json != 'undefined'){
                            currentStudents = json.users.concat()

                            // Accept csv files
                            var linesIn = text.replace( /,|;/g, '' ).toLowerCase().split( '\n' ),
                                lines = []
    
                            // Strip spaces
                            linesIn.forEach( ( line ) => {
                                lines.push( line.replace(/\s/g, '' ) )
                            })
    
                            // Dry run 
                            var candidatesForRemoval = []
    
                            // Is this user in the file?
                            currentStudents.forEach( ( user ) => {
                                if( lines.indexOf( user.user ) == -1 )
                                    candidatesForRemoval.push( user.user )
                            })
    
                            // Add students on the list who are new
                            var additions = []
                            lines.forEach( ( user ) => {
                                var fnd = currentStudents.find( ( item ) => {
                                    return item.user == user
                                })
                                if( ! fnd && user )
                                    additions.push( user )
                            })
    
                            // Avoid removal of more then 1/4 of all current students
                            var valid = candidatesForRemoval.length / json.users.length < 0.25;
    
                            if( remove && ! valid )
                                this.outputSync.removedStatus = msgNoRemovalDone
    
                            this.removals = candidatesForRemoval
    
                            // Remove students not on the list 
                            if( valid && remove ){
                                candidatesForRemoval.reduce( ( previous ) => {
                                    previous.then( this.removeStudent )
                                }, Promise.resolve( { msg: msgStartRemovingStudents } ) )
                                .then( showOutputSyncRemoval )
                                .then( () => {
                                    this.addStudents( additions, password )
                                })
                            }
                            else{
                                this.addStudents( additions, password )
                            }
                        }
                    })
                })
            }
        },
    },
    template: `<div class='ok'>
                <div class='enter-credentials syncStudents'>

                    <div class='errorMsg'></div>

                    <div v-if='outputSync'>
                        <div v-for='error in outputSync.addedErrors'><span>{{ error }}</span></div>
                        <div v-if='outputSync.removedStatus'><span>{{ outputSync.removedStatus }}</span></div>
                        <div v-if='outputSync.totalRemoved > -1'><span>{{ removedTxt }}</span>&nbsp;<span>{{ outputSync.totalRemoved }}</span></div>
                        <div v-if='outputSync.totalAdded'><span>{{ addedTxt }}</span>&nbsp;<span>{{ outputSync.totalAdded }}</span></div>
                    </div>

                    <div class='content'>
                        <div class='text'>
                            {{ titleTxt }}
                        </div>
                        <form v-on:submit.prevent='doUploadStudentsFile'>
                            <div class='field'>
                                <input type='file' required></input>
                                <label></label>
                            </div>
                            <div class='field'>
                                <div>{{ removeTxt }}</div>
                                <label class="switch">
                                    <input type="checkbox" name='remove'></input>
                                    <span class="slider round">{{ removedTxt }}</span>
                                </label>
                            </div>
                            <div class='field'>
                                <input v-on:keyup='keyUp' name='password' required type='text' v-bind:placeholder='initialPasswordTxt'></input>
                                <img v-if='! validPassword' class='icon right' src='img/cross.svg' />
                                <label></label>
                            </div>
                            <br>
                            <button>{{ submitTxt }}</button>
                        </form>
                    </div>
                </div>
            </div>`
    }
)