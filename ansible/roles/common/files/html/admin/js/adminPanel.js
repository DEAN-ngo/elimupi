(function(){

    new Vue({
    el: '#interface',
    data:{
        passwordCandidate: null,
        password: null,
        userCandidate: null,
        user: null,
        page: null,
        selectedUser: null,
        logOffExpanded: false,
        runLevelExpanded: false,
        loading: false,
        groups: [],
        actions: false,
        userListData: null,
        foundSource: false,
        hasSecondaryDisk: false,
        elimuGo: false,
        outputSyncPackages: null,
        resultCreateContentDisk: null,
        statusListPackagesForCopy: null,
        resultCopyPackagesToDisk: null,
        listPackagesForCopy: null,
        clearLocalStorageTimer: null,
        packageSpinnerClasses:[],
        installAgain: []
    },
    watch:{

        page: function( newVal, oldVal ){

            switch( newVal){
                case 'login':
                    Vue.nextTick( this.focusLoginForm )
                break

                case 'listPackages':
                    Vue.nextTick( this.doListPackages )
                break
            }

            switch( oldVal ){
                case 'resetPass':
                    this.selectedUser = null
                break
            }

        }

    },
    mounted(){
        var ls = this.getCredentialsFromLocalStorage();

        if( ls ){
            this.user = ls.user
            this.password = ls.password
            this.userCandidate = null
            this.passwordCandidate = null
            this.groups = ls.groups
            this.actions = this.groups.indexOf('teachers') > -1
            if( document.location.hash == '#turnOff')
                this.runLevelExpanded = true

            this.refreshLocalStorageTimer()
        }

        if( document.location.hash == '#show-packages')
            this.pages = 'listPackages';
        else if( document.location.hash == '#download-packages')
            this.page = 'syncPackages';
        else if( document.location.hash == '#log-off')
            ;
        else if( document.location.hash == '#list-users')
            this.page = 'listUsers';
        else if( document.location.hash == '#create-users')
            this.page = 'syncStudents';
        else if( document.location.hash == '#reset-password')
            this.page = 'resetPass'
        else if( document.location.hash == '#disk-usage')
            this.page = 'diskUsage';
        else if( document.location.hash == '#version-info')
            ;
        else if( document.location.hash == '#download-logs')
            ;

        else if( document.location.hash == '#login' 
            || document.location.hash == '#create-account' 
            || document.location.has == '#update-password')
            if(ls)
                this.page = 'listPackages'
            else
                this.page = 'login'
        else
            this.page = 'listPackages'
    },
    methods: {

        back: function(){
            var inIframe = window.self !== window.top
            if( this.page == 'viewUser')
                this.page = 'listUsers'
            else{
                if(inIframe)
                    window.parent.history.go(-1)
                else
                    document.location = '../'
            }
        },

        viewUser: function( user ){
            if( this.isAdmin() || ( this.isTeacher() && user.group == 'students')){
                this.page = 'viewUser'
                this.selectedUser = user
            }
        },

        resetPassword: function( user ){
            this.page = 'resetPass'
            this.selectedUser = user
        },

        showLoginForm: function(){
            if( this.page == 'login' )
                this.page = 'listPackages'
            else
                this.page = 'login'
        },

        closePanels: function(){
            this.logOffExpanded = false
            this.runLevelExpanded = false
        },

        cmd: function(command, params){
            return this.issueCommand(command, params)
        },

        focusLoginForm: function(){
            var el = document.querySelector( 'form.login' )
            if( el )
                el.querySelector( 'input[name=user]' ).focus()
        },

        doListPackagesForCopy: function(){
            this.page = 'listPackagesForCopy'
            issueCommand( 'getAllPackagesForSelectionCopy', { targetDisk: '', lang: this.getTranslationLanguageCode()} )
            .then( ( json ) => {
                if( json.status == 'error' )
                    this.statusListPackagesForCopy = json.msg
                else{
                    this.statusListPackagesForCopy = ''
                    this.listPackagesForCopy = {}
                    this.listPackagesForCopy.new = json.newPackages
                    this.listPackagesForCopy.updates = json.updatePackages
                }
            })
        },

        doCopyPackagesToSecondaryDisk: function(){
            var selected = document.querySelectorAll( '#selectedPackagesForCopy input[type=checkbox]:checked'),
                packages = [];

            if( selected ){
                selected.forEach( ( el ) => {
                    packages.push( el.getAttribute( 'name' ))
                })  

                issueCommand( 'copyPackagesToSecondaryDisk', { packages: packages } )
                .then( ( json ) => {
                    this.resultCopyPackagesToDisk = json.msg
                })
            }
        },

        doCreateContentDisk: function(){
            issueCommand( 'createContentDisk' )
            .then( ( json ) => {
                this.page = ''
                this.resultCreateContentDisk = json.msg
            })
        },

        expandLogOff: function( event ){
            if( event )
                event.stopPropagation()
            this.logOffExpanded = ! this.logOffExpanded
            this.runLevelExpanded = false
        },

        expandRunLevel: function( event ){
            if( event )
                event.stopPropagation()
            this.runLevelExpanded = ! this.runLevelExpanded
            this.logOffExpanded  = false
        },

        doListPackages: function(){
            Vue.nextTick( () => {
                this.packagesList = true
                Vue.nextTick( () => { 
                    this.makePackagesList( this.getTranslationLanguageCode(), this.isTeacher() ? 'all' : 'reduced', '' ) 
                })
            })
        },

        getTranslationLanguage: function(){
            return document.querySelector( 'html' ).getAttribute( 'lang' );
        },

        getTranslationLanguageCode: function(){
            var lang = this.getTranslationLanguage(),
                code = lang.split( '-' );

            return code[0]
        },

        searchPackages: function(){
            var form = document.querySelector( '#packagesList form'),
                lang = this.getTranslationLanguageCode(),
                select = this.isTeacher() ? 'all' : '',
                search = form.querySelector( 'input[name=search]' ).value;
            this.packagesList = true
            Vue.nextTick( () => {
                this.showPackagesList( this.packagesXsl, this.packagesXml, lang, select, search )
            })
        },

        makePackagesList: function( lang, select, search ){
            this.loading = true
            this.issueXmlCommand( 'getPackagesXml' )
            .then( response => response.text() )
            .then( str => (new window.DOMParser()).parseFromString(str, "text/xml"))
            .then( ( xml ) => {  
                fetch( 'packages.xsl' )
                .then( response => response.text() )
                .then( ( xsl ) => {

                    this.loading = false
                    var xsl = new window.DOMParser().parseFromString( xsl, "text/xml" )

                    this.packagesXml = xml
                    this.packagesXsl = xsl

                    this.showPackagesList( xsl, xml, lang, select, search )
                })
            })
            .catch( error => console.log( error ))
        },

        showPackagesList: function( xsl, xml, lang, select, search ){
            var result = new XSLTProcessor();
        
            xsl.querySelector( '*[name=lang]' ).innerHTML = lang
            
            xsl.querySelector( '*[name=search]' ).innerHTML = ''

            if( search )
                xsl.querySelector( '*[name=search]' ).innerHTML = search

            if( select )
                xsl.querySelector('*[name=select]').innerHTML = select

            result.importStylesheet( xsl );

            result = result.transformToDocument( xml );

            var root = result.querySelector( 'items' ),
                errors = result.querySelector( 'errors' )

            document.querySelector( '#packagesList div.list' ).innerHTML = ''

            if( errors )
                document.querySelector( '#packagesList div.list' ).appendChild( errors )
            else if( result.querySelector( 'item' ))
                document.querySelector( '#packagesList div.list' ).appendChild( root )
            else
                document.querySelector( '#packagesList div.list' ).innerHTML = msgNothingFound

            var dates = document.querySelectorAll( 'date' ),
                viewables = document.querySelectorAll( 'a.viewable' ),
                downloadables = document.querySelectorAll( 'a.downloadable' ),
                installables = document.querySelectorAll( 'img.installable' )

            if( dates ){
                dates.forEach( ( d ) => {
                    var relTimeDays = Math.floor( (new Date() - new Date( d.innerHTML )) / 1000 / 60 / 60 / 24 )

                    if( relTimeDays > 364 ){
                        var years = Math.floor( relTimeDays / 365 )
                        if( years == 1 )
                            d.innerHTML = years + ' ' + msgOneYearAgo
                        else    
                            d.innerHTML =  years + ' ' + msgYearsAgo
                    }
                    else if( relTimeDays > 30 ){
                        var months = Math.floor( relTimeDays / 31 )
                        if( months == 1 )
                            d.innerHTML = months +  ' ' + msgOneMonthAgo
                        else
                            d.innerHTML =  months + ' ' + msgMonthsAgo
                    }
                    else if( relTimeDays > 6 ){
                        var weeks = Math.floor( relTimeDays / 7 )
                        if( weeks == 1)
                            d.innerHTML = weeks + ' ' + msgOneWeekAgo
                        else 
                            d.innerHTML = days + ' ' + msgWeeksAgo
                    }
                    else if( relTimeDays > 1 )
                        d.innerHTML = relTimeDays + ' ' + msgDaysAgo
                    else if( relTimeDays == 1)
                        d.innerHTML = relTimeDays + ' ' + msgOneDayAgo
                    else if( relTimeDays == 0 )
                        d.innerHTML = msgToday
                })
            }
            if( viewables ){
                viewables.forEach( ( a ) => {

                    // Fix links
                    a.setAttribute('href', decodeURIComponent( a.getAttribute('href')))

                    a.innerHTML = msgViewable
                })
            }
            if( downloadables ){
                downloadables.forEach( ( a ) => {
                    a.innerHTML = msgDownloadable
                })
            }
            if( installables ){
                installables.forEach( ( img ) => {
                    img.setAttribute( 'title', msgInstallable )
                    img.addEventListener( 'click', (event) => { 
                        event.stopPropagation()
                        var item = event.target.closest('ul'),
                            id = img.getAttribute('rel'),
                            type = img.getAttribute('type'),
                            has = this.installAgain.findIndex( ( pack ) => { return pack.uniqueId == id })

                        if( item.classList.contains('selected-for-install'))
                            item.classList.remove( 'selected-for-install' )
                        else
                            item.classList.add( 'selected-for-install' )

                        if( has  > -1 )
                            this.installAgain.splice( has, 1 )
                        else
                            this.installAgain.push( { uniqueId: id, type: type } )
                    })
                })
            }
        },

        installPackagesAgain: function(){
            this.loading = true
            this.issueCommand( 'install', { packages: this.installAgain })
            .then( () => {
                this.loading = false
                this.installAgain = []
            })
        },

        changeRunLevel: function( event ){

            this.loading = true

            var value = parseInt( event.target.value )

            if( value == 6 ){
                this.issueCommand( 'runLevel6', null )
                .then( showResultRunLevel6 )
            }
            else if( value == 0 ){
                this.clearCredentialsInLocalStorage()
                this.issueCommand( 'runLevel0', null )
                .then( showResultRunLevel0 )
            }
            else 
                this.loading = false
        },

        changeRunLevel0: function(){
            this.loading = true
            this.showResultRunLevel0()
            this.issueCommand( 'runLevel0', null )
        },

        changeRunLevel6: function(){
            this.loading = true
            this.issueCommand( 'runLevel6', null )
            .then( showResultRunLevel6 )
        },

        disksChanged: function( hasSecondDisk ){
            this.hasSecondaryDisk = hasSecondDisk
        },

        // Users can change their password 
        doChangePassword: function(){

            this.page = 'changePassword'
        },

        logOff: function(){
            this.clearCredentialsInLocalStorage()
            document.location.reload()
        },

        doTurnOff: function(){
            this.runlevel = true
        },

        isValidPassword: function( text ){
            if( text.length > 31 || text.match( /'|"|`/ )) {
                this.validPassword = false
                return false
            }
            else{
                this.validPassword = true
                return true
            }
        },

        doLogin: function(){
            this.userCandidate =  document.querySelector( '.login form input[name=user]' ).value
            this.passwordCandidate = document.querySelector( '.login form input[name=password]' ).value

            this.loginForm = false

            this.loading = true

        },

        showResultLogin: function ( json ){
            this.loading = false

            this.user = json.username

            this.groups = json.groups

            this.actions = this.groups.indexOf('students') == -1
        },

        changePassword: function ( json ){
            this.user = this.userCandidate
            this.password = this.passwordCandidate 
            this.showResultLogin( json )
            this.afterValidLogin()
        },

        passwordUpdated: function( passwd ){
            this.password = passwd
        },

        afterValidLogin: function(){
            this.storeCredentialsInLocalStorage()
            if( document.location.hash == '#create-account' 
                && this.actions )
                this.page = 'addUser'
            else if( document.location.hash == '#update-password')
                this.doChangePassword()
            else
                this.page = 'listPackages'
        },

        isTeacher: function(){
            return this.groups.indexOf( 'teachers' ) > -1 || this.groups.indexOf( 'admins' ) > -1 || this.groups.indexOf( 'sudo' ) > -1
        },

        isAdmin: function(){
            return this.groups.indexOf( 'admins' ) > - 1 || this.groups.indexOf( 'sudo' ) > -1
        },

        isSudo: function(){
            return this.groups.indexOf( 'sudo' ) > -1
        },

        storeCredentialsInLocalStorage(){
            localStorage.setItem( 'credentials', JSON.stringify( {user: this.user, password: this.password, groups: this.groups } ))
            this.refreshLocalStorageTimer()
        },

        refreshLocalStorageTimer(){
            clearInterval( this.clearLocalStorageTimer )
            this.clearLocalStorageTimer = setInterval( this.clearCredentialsInLocalStorage , 1000 * 60 * 60 * 60 * 2 )
        },

        getCredentialsFromLocalStorage(){
            var cr = localStorage.getItem( 'credentials' )
            if( cr ){
                return JSON.parse( cr )
            }
            else
                return false
        },

        clearCredentialsInLocalStorage(){
            localStorage.removeItem( 'credentials' )
        },

        makeFormData: function( obj, params ){
            var fd = new FormData()
            for ( var o in obj )
                fd.append( o, obj[ o ])
            if( params ){
                for( var o in params )
                    if( params[ o ] instanceof Array)
                        fd.append( o, JSON.stringify( params[ o ]));
                    else
                        fd.append( o, params[ o ])
            }
            return fd
        },
        
        issueXmlCommand: function( command ){
            return new Promise( ( resolve ) => {
                fetch('request.php', {
                    method: 'POST',
                    body: this.makeFormData( {
                        command: command
                    } )
                })
                .then( resolve )
            })
        },
        
        issueCommand: function( command, params ){

            return new Promise( ( resolve, reject ) => {
                fetch('request.php', {
                    method: 'POST',
                    credentials: 'include',
                    body: this.makeFormData( { 
                        user: this.user ? this.user: this.userCandidate, 
                        password: this.password ? this.password : this.passwordCandidate, 
                        command: command,
                    }, params )
                })
                .then( ( response ) => { 
                    
                    if( response.status == 304 ) 
                        throw( 304 )
                    else if( response.status == 200 ){
                        this.refreshLocalStorageTimer()
                        return response.json() 
                    }
                })
                .then( ( json ) => { 
                        resolve( json ) 
                })
                .catch( ( error ) => { 

                    reject( error )
                    if( error == 304 ){
                        //document.location.reload()
                    }
                })
            })
        },

        getRunLevel: function(){
            return document.querySelector( '#runlevel' )
        },
        
        showResultRunLevel6: function( json ){

            if( json.msg == false){
        
                // Wait 15 seconds before polling every 2 seconds
                // to see if it is restarted 
                this.loading = true
                var t2 = setTimeout( () => {
                    var t1 = setInterval( () => {
                        fetch( 'request.php' )
                        .then( ( response ) => {
                            if( response.status == 200 ){
                                clearInterval( t1 )
                                clearTimeout( t2 )
                                this.loading = false
                                this.getRunLevel().value = '5'
                            }
                        })
                        .catch( () => {
                        })
                    }, 2000)
                }, 15000)
            }
            else if( json.msg.length > 0)
                alert( json.msg )
        },
        
        showResultRunLevel0: function( json ){
            
            setTimeout( () => {
                fetch( 'request.php' )
                .then( ( response ) => {
                    if( response.status != 200 ){
                        this.delayedLoaderOff()
                    }
                    else
                        this.showResultRunLevel0()
                })
                .catch( () => {
                    this.delayedLoaderOff()
                })
            }, 250 )
        },

        delayedLoaderOff: function(){
            setTimeout( () => {
                this.loading = false
                this.user = null
                this.password = null
                this.page = 'login'
            }, 10000)
        }
    }
    })
})()