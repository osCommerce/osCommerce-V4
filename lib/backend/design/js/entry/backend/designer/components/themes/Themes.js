import React, { useLayoutEffect, useState } from 'react';
import "./themes.scss";
import fetchData from 'src/fetch-data';
import globals from 'src/globals';
import { ReactSortable } from "react-sortablejs";
import Modal from '../Modal';
import CreateTheme from './CreateTheme';
import { connect } from 'react-redux';
import { saveSetting, fetchThemes } from '../../reducers/designer.actions';
import { toggleWindow } from '../../reducers/toolWindows.actions';
import { showButton, hideButton } from '../../reducers/topButtons.actions';

class Themes extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            themes: []
        };

        this.lastEvent = '';

        this.comp = {
            CreateTheme
        };

        this.windowResize = this.windowResize.bind(this);
        this.getSizes = this.getSizes.bind(this);
    }

    componentDidMount(){
        this.props.fetchThemes();

        this.props.addThemeButton();

        window.addEventListener('resize', this.windowResize)
    }

    componentWillUnmount() {
        this.props.removeThemeButton();
        window.removeEventListener('resize', this.windowResize);
    }

    windowResize(){
        this.setState({areaWidth: this.themeContainer.ref.current.clientWidth});
        this.setState({areaHeight: this.themeContainer.ref.current.clientHeight});
    }

    sortThemes(newState){
        this.props.saveSetting('themes', newState);
        if (this.lastEvent === 'onUpdate'){
            const list = newState.map(theme => theme.id);
            fetchData('themes', 'sort-order', list, {method: 'POST'})
        }
        this.lastEvent = 'setList';
    }

    components(component){
        return React.createElement(this.comp[component]);
    }

    getSizes(){
        if (
            !this.themeContainer ||
            !this.themeContainer.ref ||
            !this.themeContainer.ref.current
        ) {
            return [22, 20]
        }
        const containerWidth = this.themeContainer.ref.current.parentElement.clientWidth;
        const containerHeight = this.themeContainer.ref.current.parentElement.clientHeight;

        let divider = 1;
        if (containerWidth > 300 && containerHeight > 300) {
            let min = containerWidth < containerHeight ? containerWidth : containerHeight;
            divider = 1 + (min - 300) / 300;
        }

        const size = (((containerHeight - 85) * 1.34) / containerWidth) * 100 / divider;
        const margin = 10 + (containerWidth * size / 3000);
        const lineHeight = margin * 1.5;
        const width = (((containerHeight - margin - lineHeight - 25) * 1.34) / containerWidth) * 100 / divider;

        let font = 10 + (containerWidth * width / 3000);
        if (font > 20) font = 20;

        return [width, font, margin, lineHeight]
    }

    render() {
        const [itemWidth , titleFontSize, margin, lineHeight] = this.getSizes();
        const itemStyles = {
            width: itemWidth + '%',
            marginBottom: margin + 'px'
        };
        const titleStyles = {
            fontSize: titleFontSize + 'px',
            lineHeight: lineHeight + 'px'
        };

        const themeList = [...this.props.themes, {title: 'Add theme', theme_name: 'addTheme', id: 0}];

        return (
            <>
                <ReactSortable
                    className="theme-list"
                    list={themeList}
                    setList={newState => this.sortThemes(newState.filter(item => item.theme_name !== 'addTheme'))}
                    onUpdate={evt => this.lastEvent = 'onUpdate'}
                    ref={ (themeContainer) => { this.themeContainer = themeContainer } }
                    filter=".add-theme"
                >
                    {themeList.map(theme => (
                        theme.theme_name === 'addTheme' ? (
                            <div key={theme.id} className="item add-theme" style={itemStyles} onClick={() => this.props.addTheme()}>
                                <div className="img">
                                    <div className="add-theme-ico">+</div>
                                </div>
                                <div className="title" style={titleStyles}>
                                    {theme.title}
                                </div>
                            </div>
                        ) : (
                            <div key={theme.id} className="item" style={itemStyles}>
                                <div className="img">
                                    <a href={globals(['baseUrl']) + 'design/elements?theme_name=' + theme.theme_name}>
                                        <img src={globals(['frontendUrl']) + 'themes/' + theme.theme_name + '/screenshot.png'} alt={theme.title}/>
                                    </a>
                                </div>
                                <div className="title" style={titleStyles}>
                                    <a href={globals(['baseUrl']) + 'design/elements?theme_name=' + theme.theme_name}>
                                        {theme.title}
                                    </a>
                                </div>
                            </div>
                        )
                    )) }
                </ReactSortable>
                <Modal>
                    {this.components('CreateTheme')}
                </Modal>
            </>
        );
    }
}

const mapStateToProps = (state, ownProps) => ({
    addTheme: state.topButtons.addTheme,
    themes: state.designer.themes,
});

const mapDispatchToProps = (dispatch, ownProps) => ({
    addThemeButton: () => dispatch(showButton('addTheme')),
    removeThemeButton: () => dispatch(hideButton('addTheme')),
    addTheme: () => dispatch(toggleWindow('addTheme')),
    saveSetting: (name, value) => dispatch(saveSetting(name, value)),
    fetchThemes: () => dispatch(fetchThemes()),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Themes)