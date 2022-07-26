import React from 'react';
import { connect } from 'react-redux';
import Tools from './Tools';
import { ReactSortable } from "react-sortablejs";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faWindowMaximize } from '@fortawesome/free-regular-svg-icons';
import css from './Area.scss';
import { saveSetting, saveSubSetting, closeTool, toPopUp } from '../reducers/designer.actions';

class Area extends React.Component {
    constructor(props) {
        super(props);

        this.lastEvent = '';
    }

    sortThemes(newState){
        if (this.lastEvent === 'onUpdate'){
            this.props.saveSubSettings('toolsList', this.props.areaName, newState)
        }
        this.lastEvent = 'setList';
    }

    activeTool(){
        if (this.props.toolsList.find(item => item.name === this.props.activeTool)) {
            return this.props.activeTool;
        } else if (this.props.toolsList.length) {
            return this.props.toolsList[0].name;
        } else {
            return '';
        }
    }

    render() {
        return (
            <div className="designer-area">
                <ReactSortable
                    className="area-headings"
                    list={this.props.toolsList}
                    setList={newState => this.sortThemes(newState)}
                    onUpdate={evt => this.lastEvent = 'onUpdate'}
                    onAdd={evt => this.lastEvent = 'onUpdate'}
                    onRemove={evt => this.lastEvent = 'onUpdate'}
                    group="toolsList"
                    onStart={() => this.props.saveSetting('toolMoves', true)}
                    onEnd={() => this.props.saveSetting('toolMoves', false)}
                >
                    {this.props.toolsList.map(item => (
                        <div className={'tab-title' + (item.name === this.props.activeTool ? ' active' : '')} key={item.name}>
                            <span className="text"
                                  onClick={() => this.props.saveSubSettings('activeTool', this.props.areaName, item.name)}
                            >
                                {item.name}
                            </span>
                            <span className="btn-to-popup" onClick={() => this.props.toPopUp(item.name)}>
                                <FontAwesomeIcon icon={faWindowMaximize} />
                            </span>
                            <span className="btn-close" onClick={() => this.props.closeTool(item.name)}>
                                <i className="icon-close"></i>
                            </span>
                        </div>
                    ))}
                </ReactSortable>
                <div className="area-content">
                    <Tools name={this.activeTool()} areaName={this.props.areaName}/>
                </div>
            </div>
        );
    }
}


const mapStateToProps = (state, ownProps) => {
    return {
    areaName: ownProps.areaName,
    toolsList: state.designer.toolsList[ownProps.areaName] ? state.designer.toolsList[ownProps.areaName] : [],
    activeTool: state.designer.activeTool[ownProps.areaName] ? state.designer.activeTool[ownProps.areaName] : null,
}};

const mapDispatchToProps = (dispatch, ownProps) => ({
    saveSubSettings: (name, subName, value) => dispatch(saveSubSetting(name, subName, value)),
    saveSetting: (name, value) => dispatch(saveSetting(name, value)),
    closeTool: (toolName) => dispatch(closeTool( ownProps.areaName, toolName)),
    toPopUp: (toolName) => dispatch(toPopUp(ownProps.areaName, toolName)),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Area)