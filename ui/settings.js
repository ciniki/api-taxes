//
function ciniki_taxes_settings() {
    this.toggleOptions = {'no':'Off', 'yes':'On'};

    this.typeFlags = {'1':{'name':'Inactive'}};
    this.init = function() {
        //
        // The menu panel
        //
        this.menu = new M.panel('Settings',
            'ciniki_taxes_settings', 'menu',
            'mc', 'narrow', 'sectioned', 'ciniki.taxes.settings.menu');
        this.menu.sections = {
            'taxes':{'label':'Taxes', 'list':{
                'types':{'label':'Tax Types', 'fn':'M.ciniki_taxes_settings.showTypes(\'M.ciniki_taxes_settings.showMenu();\');'}, 
                'locations':{'label':'Tax Locations', 'visible':'no', 'fn':'M.ciniki_taxes_settings.showLocations(\'M.ciniki_taxes_settings.showMenu();\');'}, 
                'taxes':{'label':'Tax Rates', 'fn':'M.ciniki_taxes_settings.showRates(\'M.ciniki_taxes_settings.showMenu();\');'},
                }},
        };
        this.menu.addClose('Back');

        //
        // The tax types panel
        //
        this.taxtypes = new M.panel('Tax Types',
            'ciniki_taxes_settings', 'taxtypes',
            'mc', 'medium', 'sectioned', 'ciniki.taxes.settings.taxtypes');
        this.taxtypes.sections = {
            'active':{'label':'Active Tax Types', 'type':'simplegrid', 'num_cols':2,
                'headerValues':['Type', 'Rates'],
                'cellClasses':['',''],
                'addTxt':'Add Tax',
                'addFn':'M.ciniki_taxes_settings.editType(\'M.ciniki_taxes_settings.showTypes();\',0)',
                },
            'inactive':{'label':'Inactive Tax Types', 'visible':'no', 'type':'simplegrid', 'num_cols':2,
                'headerValues':['Type', 'Rates'],
                'cellClasses':['',''],
                },
        };
        this.taxtypes.sectionData = function(s) { return this.data[s]; }
        this.taxtypes.cellValue = function(s, i, j, d) {
            if( j == 0 ) { return d.type.name; }
            if( j == 1 ) {
                if( d.type.rates != null ) {
                    var txt = '';
                    for(i in d.type.rates) {
                        txt += d.type.rates[i].rate.location + (d.type.rates[i].rate.location!=''?' - ':'') + d.type.rates[i].rate.name + '<br/>';
                    }
                    return txt;
                }
                return '';
            }
        };
        this.taxtypes.rowFn = function(s, i, d) {
            return 'M.ciniki_taxes_settings.editType(\'M.ciniki_taxes_settings.showTypes();\',\'' + d.type.id + '\');';
        };
        this.taxtypes.addClose('Back');

        //
        // The locations panel
        //
        this.locations = new M.panel('Locations',
            'ciniki_taxes_settings', 'locations',
            'mc', 'medium', 'sectioned', 'ciniki.taxes.settings.locations');
        this.locations.sections = {
            'locations':{'label':'Locations', 'type':'simplegrid', 'num_cols':3,
                'headerValues':['Location', 'Country', 'Constraints'],
                'cellClasses':['',''],
                'sortable':'yes',
                'sortTypes':['text','text','text','text'],
                'addTxt':'Add Location',
                'addFn':'M.ciniki_taxes_settings.editLocation(\'M.ciniki_taxes_settings.showLocations();\',0)',
                },
        };
        this.locations.sectionData = function(s) { return this.data[s]; }
        this.locations.cellValue = function(s, i, j, d) {
            switch (j) {
                case 0: return ((d.location.code!=null&&d.location.code!='')?d.location.code+' - ':'') + d.location.name;
//              case 0: return d.location.name;
                case 1: return d.location.country_code;
                case 2: return d.location.constraints;
            }
        };
        this.locations.rowFn = function(s, i, d) {
            return 'M.ciniki_taxes_settings.editLocation(\'M.ciniki_taxes_settings.showLocations();\',\'' + d.location.id + '\');';
        };
        this.locations.addButton('add', 'Add', 'M.ciniki_taxes_settings.editLocation(\'M.ciniki_taxes_settings.showLocations();\',0);');
        this.locations.addClose('Back');

        //
        // The taxes panel
        //
        this.taxrates = new M.panel('Taxes',
            'ciniki_taxes_settings', 'taxrates',
            'mc', 'medium', 'sectioned', 'ciniki.taxes.settings.taxrates');
        this.taxrates.sections = {
            'current':{'label':'Current Taxes', 'type':'simplegrid', 'num_cols':2,
                'headerValues':['Tax/types', 'Start/End'],
                'cellClasses':['multiline','multiline'],
                'sortable':'yes',
                'sortTypes':['text', 'text', 'date'],
                'addTxt':'Add Tax',
                'addFn':'M.ciniki_taxes_settings.editRate(\'M.ciniki_taxes_settings.showRates();\',0)',
                },
            'future':{'label':'Future Taxes', 'visible':'no', 'type':'simplegrid', 'num_cols':2,
                'headerValues':['Tax/types', 'Start/End'],
                'cellClasses':['multiline','multiline'],
                },
            'past':{'label':'Past Taxes', 'visible':'no', 'type':'simplegrid', 'num_cols':2,
                'headerValues':['Tax/types', 'Start/End'],
                'cellClasses':['multiline','multiline'],
                },
        };
        this.taxrates.sectionData = function(s) { return this.data[s]; }
        this.taxrates.cellValue = function(s, i, j, d) {
            if( (M.curTenant.modules['ciniki.taxes'].flags&0x01) > 0 ) {
                switch (j) {
                    case 0: return '<span class="maintext">' + d.rate.location_name + '</span>';
                    case 1: return '<span class="maintext">' + d.rate.name + '</span><span class="subtext">' + d.rate.types + '</span>';
                    case 2: return '<span class="maintext">' + d.rate.start_date + '</span><span class="subtext">' + d.rate.end_date + '</span>';
                }
            } else {
                switch (j) {
                    case 0: return '<span class="maintext">' + d.rate.name + '</span><span class="subtext">' + d.rate.types;
                    case 1: return '<span class="maintext">' + d.rate.start_date + '</span><span class="subtext">' + d.rate.end_date + '</span>';
                }
            }
        };
        this.taxrates.rowFn = function(s, i, d) {
            return 'M.ciniki_taxes_settings.editRate(\'M.ciniki_taxes_settings.showRates();\',\'' + d.rate.id + '\');';
        };
        this.taxrates.addClose('Back');

        //
        // The add/edit panel for tax types
        //
        this.typeedit = new M.panel('Tax Type',
            'ciniki_taxes_settings', 'typeedit',
            'mc', 'medium', 'sectioned', 'ciniki.taxes.settings.typeedit');
        this.typeedit.type_id = 0;
        this.typeedit.data = {};
        this.typeedit.sections = {
            'type':{'label':'', 'fields':{
                'name':{'label':'Name', 'type':'text'},
                'flags':{'label':'Flags', 'type':'flags', 'flags':this.typeFlags},
                }},
            'current':{'label':'Current Rates', 'visible':'no', 'fields':{}},
            'future':{'label':'Future Rates', 'visible':'no', 'fields':{}},
            'past':{'label':'Past Rates', 'visible':'no', 'fields':{}},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_taxes_settings.saveType();'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_taxes_settings.deleteType();'},
                }},
        };
        this.typeedit.fieldValue = function(s, i, d) {
            if( this.data[s] != null && this.data[s][i] != null ) { return this.data[s][i]; }
            if( s == 'current' || s == 'future' || s == 'past' ) {
                if( this.data.rate_ids != null && this.data.rate_ids.indexOf(i) > -1 ) { 
                    return 'yes'; 
                } else {
                    return 'no';
                }
            }
            return '';
        };
        this.typeedit.fieldHistoryArgs = function(s, i) {
            if( s == 'current' || s == 'future' || s == 'past' ) {
                return {'method':'ciniki.taxes.history', 'args':{'tnid':M.curTenantID,
                    'object':'ciniki.taxes.type', 'object_id':this.type_id, 'field':'rate_id', 'field_value':i}};
            }
            return {'method':'ciniki.taxes.history', 'args':{'tnid':M.curTenantID,
                'object':'ciniki.taxes.type', 'object_id':this.type_id, 'field':i}};
        }
        this.typeedit.addClose('Cancel');   

        //
        // The add/edit panel for tax rates
        //
        this.location = new M.panel('Tax Location',
            'ciniki_taxes_settings', 'location',
            'mc', 'medium', 'sectioned', 'ciniki.taxes.settings.location');
        this.location.location_id = 0;
        this.location.data = {};
        this.location.sections = {
            'location':{'label':'', 'fields':{
                'name':{'label':'Name', 'type':'text'},
                'code':{'label':'Code', 'type':'text'},
                'country_code':{'label':'Country Code', 'type':'text', 'size':'small'},
                'start_postal_zip':{'label':'Start Postal Zip', 'type':'text', 'size':'small'},
                'end_postal_zip':{'label':'End Postal Zip', 'type':'text', 'size':'small'},
                }},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_taxes_settings.saveLocation();'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_taxes_settings.deleteLocation();'},
                }},
        };
        this.location.fieldValue = function(s, i, d) {
            if( this.data[s] != null && this.data[s][i] != null ) { return this.data[s][i]; }
            return '';
        };
        this.location.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.taxes.history', 'args':{'tnid':M.curTenantID,
                'object':'ciniki.taxes.location', 'object_id':this.location_id, 'field':i}};
        }
        this.location.addClose('Cancel');   

        //
        // The add/edit panel for tax rates
        //
        this.rateedit = new M.panel('Tax Rate',
            'ciniki_taxes_settings', 'rateedit',
            'mc', 'medium', 'sectioned', 'ciniki.taxes.settings.rateedit');
        this.rateedit.rate_id = 0;
        this.rateedit.data = {};
        this.rateedit.sections = {
            'rate':{'label':'', 'fields':{
                'name':{'label':'Name', 'type':'text'},
                'location_id':{'label':'Location', 'active':'no', 'type':'select', 'options':{}},
                'item_percentage':{'label':'Item %', 'type':'text', 'size':'small'},
                'item_amount':{'label':'Item Amount', 'type':'text', 'size':'small'},
                'invoice_amount':{'label':'Invoice Amount', 'type':'text', 'size':'small'},
                'flags':{'label':'Options', 'type':'flags', 
                    'visible':function() { return M.modFlagSet('ciniki.taxes', 0x10); },
                    'flags':{'1':{'name':'Tax Included'}},
                    },
                'start_date':{'label':'Start Date', 'type':'text', 'size':'medium', 'hint':'2000-01-01 00:00:00'},
                'end_date':{'label':'End Date', 'type':'text', 'size':'medium', 'hint':'1999-12-31 23:59:59'},
                }},
            'active':{'label':'Active Tax Types', 'visible':'no', 'fields':{}},
            'inactive':{'label':'Inactive Tax Types', 'visible':'no', 'fields':{}},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_taxes_settings.saveRate();'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_taxes_settings.deleteRate();'},
                }},
        };
        this.rateedit.fieldValue = function(s, i, d) {
            if( s == 'active' || s == 'inactive' ) {
                if( this.data.type_ids != null && this.data.type_ids.indexOf(i) > -1 ) { 
                    return 'yes'; 
                } else {
                    return 'no';
                }
            }
            if( this.data[s] != null && this.data[s][i] != null ) { return this.data[s][i]; }
            return '';
        };
        this.rateedit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.taxes.history', 'args':{'tnid':M.curTenantID,
                'object':'ciniki.taxes.rate', 'object_id':this.rate_id, 'field':i}};
        }
        this.rateedit.addClose('Cancel');   
    };

    //
    // Arguments:
    // aG - The arguments to be parsed into args
    //
    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_taxes_settings', 'yes');
        if( appContainer == null ) {
            alert('App Error');
            return false;
        } 

        if( (M.curTenant.modules['ciniki.taxes'].flags&0x01) > 0 ) {
            this.menu.sections.taxes.list.locations.visible = 'yes';
            this.taxrates.sections.current.num_cols = 3;
            this.taxrates.sections.current.headerValues = ['Location', 'Tax/Types','Start/End'];
            this.taxrates.sections.future.num_cols = 3;
            this.taxrates.sections.future.headerValues = ['Location', 'Tax/Types','Start/End'];
            this.taxrates.sections.past.num_cols = 3;
            this.taxrates.sections.past.headerValues = ['Location', 'Tax/Types','Start/End'];
            if( (M.curTenant.modules['ciniki.taxes'].flags&0x02) > 0 ) {
                this.location.sections.location.fields.code.active = 'yes';
            } else {
                this.location.sections.location.fields.code.active = 'no';
            }
        } else {
            this.menu.sections.taxes.list.locations.visible = 'no';
            this.taxrates.sections.current.num_cols = 2;
            this.taxrates.sections.current.headerValues = ['Tax/Types','Start/End'];
            this.taxrates.sections.future.num_cols = 2;
            this.taxrates.sections.future.headerValues = ['Tax/Types','Start/End'];
            this.taxrates.sections.past.num_cols = 2;
            this.taxrates.sections.past.headerValues = ['Tax/Types','Start/End'];
            this.location.sections.location.fields.code.active = 'no';
        }

        this.showMenu(cb);
    };

    //
    // Grab the stats for the tenant from the database and present the list of orders.
    //
    this.showMenu = function(cb) {
        this.menu.refresh();
        this.menu.show(cb);
    };

    this.showTypes = function(cb) {
        M.api.getJSONCb('ciniki.taxes.typeList', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_taxes_settings.taxtypes;
            p.data = {'active':rsp.active, 'inactive':rsp.inactive};
            if( rsp.inactive.length > 0 ) {
                p.sections.inactive.visible = 'yes';
            } else {
                p.sections.inactive.visible = 'no';
            }
            p.refresh();
            p.show(cb);
        });
    };

    this.showLocations = function(cb) {
        M.api.getJSONCb('ciniki.taxes.locationList', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_taxes_settings.locations;
            p.data = {'locations':rsp.locations};
            p.refresh();
            p.show(cb);
        });
    };

    this.showRates = function(cb) {
        M.api.getJSONCb('ciniki.taxes.rateList', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_taxes_settings.taxrates;
            p.data = {'current':rsp.current, 'future':rsp.future, 'past':rsp.past};
            if( rsp.future.length > 0 ) {
                p.sections.future.visible = 'yes';
            } else {
                p.sections.future.visible = 'no';
            }
            if( rsp.past.length > 0 ) {
                p.sections.past.visible = 'yes';
            } else {
                p.sections.past.visible = 'no';
            }
            p.refresh();
            p.show(cb);
        });
    };

    this.editType = function(cb, tid) {
        this.typeedit.reset();
        this.typeedit.data = {};
        if( tid != null ) { this.typeedit.type_id = tid; }
        M.api.getJSONCb('ciniki.taxes.rateList', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_taxes_settings.typeedit;
            var sections = ['current', 'future', 'past'];
            for(i in sections) {
                var s = sections[i];
                p.data[s] = {};
                p.sections[s].fields = {};
                for(j in rsp[s]) {
                    p.sections[s].fields[rsp[s][j].rate.id] = {
                        'label':((rsp[s][j].rate.location_name!=null&&rsp[s][j].rate.location_name!='')?rsp[s][j].rate.location_name+' - ':'')+rsp[s][j].rate.name 
                            + ' [' + rsp[s][j].rate.start_date 
                            + ' - ' + rsp[s][j].rate.end_date + ']',
                        'type':'toggle', 'default':'no', 'toggles':M.ciniki_taxes_settings.toggleOptions,
                        };
                }
                if( rsp[s] != null && rsp[s].length > 0 ) {
                    p.sections[s].visible = 'yes';
                } else {
                    p.sections[s].visible = 'no';
                }
            }
            if( p.type_id > 0 ) {
                p.sections._buttons.buttons.delete.visible = 'yes';
                M.api.getJSONCb('ciniki.taxes.typeGet', {'tnid':M.curTenantID,
                    'type_id':p.type_id}, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        if( rsp.type.rate_ids != null ) {
                            p.data.rate_ids = rsp.type.rate_ids.split(',');
                        }
                        p.data.type = rsp.type;
                        p.refresh();
                        p.show(cb);
                });
            } else {
                p.data.type = {};
                p.sections._buttons.buttons.delete.visible = 'no';
                p.refresh();
                p.show(cb);
            }
        });
    };

    this.saveType = function() {
        var rates = '';
        var cm = '';
        var sections = ['current','future','past'];
        for(i in sections) {
            var s = sections[i];
            if( this.typeedit.sections[s].visible == 'no' ) { continue; }
            for(var j in this.typeedit.sections[s].fields) {
                var v = this.typeedit.formFieldValue(this.typeedit.sections[s].fields[j], j);
                if( v == 'yes' ) {
                    rates += cm + j;
                    cm = ',';
                }
            }
        }
        if( this.typeedit.type_id > 0 ) {
            var c = this.typeedit.serializeFormSection('no', 'type');
            if( rates != this.typeedit.data.rate_ids ) {
                c += 'rate_ids=' + rates + '&';
            }
            if( c != '' ) {
                M.api.postJSONCb('ciniki.taxes.typeUpdate', {'tnid':M.curTenantID,
                    'type_id':this.typeedit.type_id}, c, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        M.ciniki_taxes_settings.typeedit.close();
                    });
            } else {
                this.typeedit.close();
            }
        } else {
            var c = this.typeedit.serializeFormSection('yes', 'type');
            c += 'rate_ids=' + rates + '&'
            M.api.postJSONCb('ciniki.taxes.typeAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_taxes_settings.typeedit.close();
            });
        }
    };

    this.deleteType = function() {
        if( confirm("Are you sure you want to delete this tax type?") ) {
            var rsp = M.api.getJSONCb('ciniki.taxes.typeDelete', {'tnid':M.curTenantID, 
                'type_id':M.ciniki_taxes_settings.typeedit.type_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_taxes_settings.typeedit.close();
                });
        }
    };

    this.editLocation = function(cb, lid) {
        if( lid != null ) { this.location.location_id = lid; }
        this.location.reset();
        this.location.data = {};
        if( this.location.location_id > 0 ) {
            this.location.sections._buttons.buttons.delete.visible = 'yes';
            M.api.getJSONCb('ciniki.taxes.locationGet', {'tnid':M.curTenantID,
                'location_id':this.location.location_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_taxes_settings.location;
                    p.data.location = rsp.location;
                    p.refresh();
                    p.show(cb);
                });
        } else {
            this.location.data.location = {};
            this.location.sections._buttons.buttons.delete.visible = 'no';
            this.location.refresh();
            this.location.show(cb);
        }
    };

    this.saveLocation = function() {
        if( this.location.location_id > 0 ) {
            var c = this.location.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.taxes.locationUpdate', {'tnid':M.curTenantID,
                    'location_id':this.location.location_id}, c, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        M.ciniki_taxes_settings.location.close();
                    });
            } else {
                this.location.close();
            }
        } else {
            var c = this.location.serializeForm('yes');
            M.api.postJSONCb('ciniki.taxes.locationAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_taxes_settings.location.close();
            });
        }
    };

    this.deleteLocation = function() {
        if( confirm("Are you sure you want to delete this tax location?") ) {
            var rsp = M.api.getJSONCb('ciniki.taxes.locationDelete', {'tnid':M.curTenantID, 
                'location_id':M.ciniki_taxes_settings.location.location_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_taxes_settings.location.close();
                });
        }
    };

    this.editRate = function(cb, rid) {
        this.rateedit.reset();
        this.rateedit.data = {};
        if( rid != null ) { this.rateedit.rate_id = rid; }
        M.api.getJSONCb('ciniki.taxes.typeList', {'tnid':M.curTenantID, 'locations':((M.curTenant.modules['ciniki.taxes'].flags&0x01)>0?'yes':'no')}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_taxes_settings.rateedit;
            p.sections.rate.fields.location_id.options = {};
            if( (M.curTenant.modules['ciniki.taxes'].flags&0x01) > 0 ) {
                if( rsp.locations != null ) {
                    p.sections.rate.fields.location_id.active = 'yes';
                    for(i in rsp.locations) {
                        p.sections.rate.fields.location_id.options[rsp.locations[i].location.id] = rsp.locations[i].location.name;
                    }
                } else {
                    p.sections.rate.fields.location_id.active = 'no';
                }
            } else {
                p.sections.rate.fields.location_id.active = 'no';
            }
            var sections = ['active','inactive'];
            for(i in sections) {
                var s = sections[i];
                p.sections[s].fields = {};
                p.data[s] = {};
                for(j in rsp[s]) {
                    p.sections[s].fields[rsp[s][j].type.id] = {
                        'label':rsp[s][j].type.name,
                        'type':'toggle', 'default':'no', 'toggles':M.ciniki_taxes_settings.toggleOptions,
                        };
                }
                if( rsp[s] != null && rsp[s].length > 0 ) {
                    p.sections[s].visible = 'yes';
                } else {
                    p.sections[s].visible = 'no';
                }
            }
            if( p.rate_id > 0 ) {
                p.sections._buttons.buttons.delete.visible = 'yes';
                M.api.getJSONCb('ciniki.taxes.rateGet', {'tnid':M.curTenantID,
                    'rate_id':p.rate_id}, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        if( rsp.rate.type_ids != null ) {
                            p.data.type_ids = rsp.rate.type_ids.split(',');
                        }
                        p.data.rate = rsp.rate;
                        p.refresh();
                        p.show(cb);
                });
            } else {
                p.data.rate = {};
                p.sections._buttons.buttons.delete.visible = 'no';
                p.refresh();
                p.show(cb);
            }
        });
    };

    this.saveRate = function() {
        var types = '';
        var cm = '';
        var sections = ['active','inactive'];
        for(i in sections) {
            var s = sections[i];
            if( this.rateedit.sections[s].visible == 'no' ) { continue; }
            for(var j in this.rateedit.sections[s].fields) {
                var v = this.rateedit.formFieldValue(this.rateedit.sections[s].fields[j], j);
                if( v == 'yes' ) {
                    types += cm + j;
                    cm = ',';
                }
            }
        }
        if( this.rateedit.rate_id > 0 ) {
            var c = this.rateedit.serializeFormSection('no', 'rate');
            if( types != this.rateedit.data.type_ids ) {
                c += 'type_ids=' + types + '&';
            }
            if( c != '' ) {
                M.api.postJSONCb('ciniki.taxes.rateUpdate', {'tnid':M.curTenantID,
                    'rate_id':this.rateedit.rate_id}, c, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        M.ciniki_taxes_settings.rateedit.close();
                    });
            } else {
                this.rateedit.close();
            }
        } else {
            var c = this.rateedit.serializeFormSection('yes', 'rate');
            c += 'type_ids=' + types + '&'
            M.api.postJSONCb('ciniki.taxes.rateAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_taxes_settings.rateedit.close();
            });
        }
    };

    this.deleteRate = function() {
        if( confirm("Are you sure you want to delete this tax rate?") ) {
            var rsp = M.api.getJSONCb('ciniki.taxes.rateDelete', {'tnid':M.curTenantID, 
                'rate_id':M.ciniki_taxes_settings.rateedit.rate_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_taxes_settings.rateedit.close();
                });
        }
    };
}
