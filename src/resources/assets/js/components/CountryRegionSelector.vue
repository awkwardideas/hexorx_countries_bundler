<template>
    <div>
        <div v-if="selectedCountry !== null" class="form-group" :class="{'has-error': hasRegionOrZipError}">
            <label :for="regionSelectName" class="control-label" :class="labelClass">{{selectedCountry.subdivision_label || regionLabel }}{{labelSuffix}}</label>
            <div :class="inputWrapClass">
                <div class="row">
                    <div class="col-lg-5">
                        <select :name="regionSelectName" :id="regionSelectName" v-model="selectedRegionAbbreviation" class="form-control">
                            <option v-for="subdivision in selectedCountry.subdivisions" :value="subdivision.abbreviation">{{subdivision.name}}</option>
                        </select>
                    </div>
                    <label :for="zipInputName" class="col-lg-2 control-label">{{zipLabel}}{{labelSuffix}}</label>
                    <div class="col-lg-5">
                        <input type="text" class="form-control" :id="zipInputName" :name="zipInputName" :placeholder="zipLabel" :value="zipValue">
                    </div>
                </div>
            </div>
            <span class="help-block" v-if="hasRegionError">
                <strong>{{ regionError }}</strong>
            </span>
            <span class="help-block" v-if="hasZipError">
                <strong>{{ zipError }}</strong>
            </span>
        </div>
        <div class="form-group" :class="{'has-error': hasCountryError}">
            <label :for="countrySelectName" class="control-label" :class="labelClass">{{countryLabel}}{{labelSuffix}}</label>
            <div :class="inputWrapClass">
                <select :id="countrySelectName" :name="countrySelectName" v-model="selectedCountryAbbreviation" class="form-control">
                    <option v-for="country in countries" :value="country.abbreviation">{{country.name}}</option>
                </select>
            </div>
            <span class="help-block" v-if="hasCountryError">
                <strong>{{ countryError }}</strong>
            </span>
        </div>
    </div>
</template>

<script>
    import json from './data/country_region.json'
    export default{
        props: {
            labelClass: {
                default: null,
            },
            inputWrapClass: {
                default: null,
            },
            labelSuffix:{
                default: '',
            },
            regionLabel:{
                default: 'Region'
            },
            regionSelectName: {
                default: 'region',
            },
            regionValue:{
                default: null,
            },
            regionError:{
                default: null,
            },
            zipLabel:{
                default: 'Zip'
            },
            zipInputName:{
                default: 'zip',
            },
            zipValue:{
                default: null,
            },
            zipError:{
                default: null,
            },
            countryLabel:{
                default: 'Country'
            },
            countrySelectName: {
                default: 'country',
            },
            countryValue:{
                default: null,
            },
            countryError:{
                default: null,
            },
            countryDefault:{
                default: 'US'
            }
        },
        data: function () {
            return {
                countries: this.sortJSONByProperty(json,"name"),
                selectedCountryAbbreviation: this.countryDefault,
                selectedRegionAbbreviation: null,
            }
        },
        mounted: function(){
            this.$nextTick(function () {
                this.selectedCountryAbbreviation = this.parseCountryAbbreviation(this.countryValue) || this.countryDefault;
                this.selectedRegionAbbreviation = this.parseRegionAbbreviation(this.regionValue);
            });
        },
        computed: {
            selectedCountry(){
                if(this.selectedCountryAbbreviation !== null){
                    return this.getCountryByAbbreviation(this.selectedCountryAbbreviation);
                }
                return null;
            },
            selectedRegion(){
                if(this.selectedRegionAbbreviation !== null){
                    return this.getRegionByAbbreviation(this.selectedCountry, this.selectedRegionAbbreviation);
                }
                return null;
            },
            hasRegionOrZipError(){
                return (this.hasRegionError || this.hasZipError);
            },
            hasRegionError(){
                return this.errorExists(this.regionError);
            },
            hasZipError(){
                return this.errorExists(this.zipError);
            },
            hasCountryError(){
                return this.errorExists(this.countryError);
            },
        },
        methods: {
            parseCountryAbbreviation(value){
                if(value === null || value === ""){
                    return null;
                }
                let country = this.getCountryByAbbreviation(value);
                if(country !== null){
                    return country.abbreviation;
                }
                country = this.getCountryByName(value);
                if(country !== null){
                    return country.abbreviation;
                }
                return null;
            },
            parseRegionAbbreviation(value){
                if(this.selectedCountry===null || value === null || value === ""){
                    return null;
                }
                let region = this.getRegionByAbbreviation(this.selectedCountry, value);
                if(region !== null){
                    return region.abbreviation;
                }
                region = this.getRegionByName(this.selectedCountry, value);
                if(region !== null){
                    return region.abbreviation;
                }
                return null;
            },

            getCountryByAbbreviation(abbreviation){
                return this.arrayGetMatch(this.countries, 'abbreviation', abbreviation);
            },
            getCountryByName(name){
                return this.arrayGetMatch(this.countries, 'name', name);
            },
            getRegionByAbbreviation(country, abbreviation){
                return this.arrayGetMatch(country.subdivisions, 'abbreviation', abbreviation);
            },
            getRegionByName(country, name){
                return this.arrayGetMatch(country.subdivisions, 'name', name);
            },
            arrayGetMatch(array, attribute, needle){
                let result = array.filter(
                    function(item){ return item[attribute].localeCompare(needle,'us-en',{sensitivity:'base'}) === 0 }
                );
                return (result.length > 0) ? result[0] : null;
            },
            sortJSONByProperty(objArray, prop, direction){
                if (arguments.length<2) throw new Error("ARRAY, AND OBJECT PROPERTY MINIMUM ARGUMENTS, OPTIONAL DIRECTION");
                if (!Array.isArray(objArray)) throw new Error("FIRST ARGUMENT NOT AN ARRAY");
                const clone = objArray.slice(0);
                const direct = arguments.length>2 ? direction : 1; //Default to ascending
                const propPath = (prop.constructor===Array) ? prop : prop.split(".");
                const no_locale = /^[\w-.\s,]*$/;
                clone.sort(function(a,b){
                    for (let p in propPath){
                        if (a[propPath[p]] && b[propPath[p]]){
                            a = a[propPath[p]];
                            b = b[propPath[p]];
                        }
                    }
                    // convert numeric strings to integers
                    a = a.match(/^\d+$/) ? +a : a;
                    b = b.match(/^\d+$/) ? +b : b;
                    if (no_locale.test(a) && no_locale.test(b)) {
                        return (a > b ? 1 : (a == b ? 0 : -1))*direct;
                    } else {
                        return a.localeCompare(b,'us-en',{sensitivity:'base'})*direct;
                    }
                });
                return clone;
            },
            errorExists(error){
                return error !== null && error !== "";
            }
        }
    }
</script>

<style lang="scss">

</style>