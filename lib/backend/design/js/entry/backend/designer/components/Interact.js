import React from 'react';
import ReactDOM from 'react-dom';
import interact from 'interactjs';

//http://jsfiddle.net/ubershmekel/sh7fqt0w/1/
export default class Interact extends React.Component {
    constructor(props) {
        super(props);

        this.dragMoveListener = this.dragMoveListener.bind(this);
        this.resizeListener = this.resizeListener.bind(this);
        this.state = {
            style: {
                width: props.width + 'px',
                height: props.height + 'px',
                transform: 'translate(' + props.dataX + 'px, ' + props.dataY + 'px)',
            },
            dataX: props.dataX || 0,
            dataY: props.dataY || 0,
            width: props.width || 0,
            height: props.height || 0
        };

        if (!this.props.resizable.onResize) {
            this.props.resizable.onResize = function () {};
        }
    }

    dragMoveListener(event) {
        const x = this.state.dataX + event.dx;
        const y = this.state.dataY + event.dy;

        this.setState({
            dataX: x,
            dataY: y,
            style: {
                width: this.state.width + 'px',
                height: this.state.height + 'px',
                transform: 'translate(' + x + 'px, ' + y + 'px)',
            }
        })
        this.props.onChange(this.state)
    }

    resizeListener(event) {
        const x = this.state.dataX + event.deltaRect.left;
        const y = this.state.dataY + event.deltaRect.top;

        this.setState({
            dataX: x,
            dataY: y,
            width: event.rect.width,
            height: event.rect.height,
            style: {
                width: event.rect.width + 'px',
                height: event.rect.height + 'px',
                transform: 'translate(' + x + 'px, ' + y + 'px)',
            }
        });
        this.props.onChange(this.state)

        this.props.resizable.onResize(event)
    }

    setupInteractable(item) {
        if (this.props.resizable) {
            item.resizable(Object.assign({
                edges: {
                    left: true,
                    right: true,
                    bottom: true,
                    top: true,
                },
                listeners: {
                    move: this.resizeListener,
                }
            }, this.props.resizable));
        }

        if (this.props.draggable) {
            item.draggable(Object.assign({
                listeners: {
                    move: this.dragMoveListener,
                }
            }, this.props.draggable));
        }
    }

    componentDidMount() {
        this.interactable = interact(this.el);
        this.setupInteractable(this.interactable);
    }

    componentWillUnmount() {
        this.interactable.unset();
        this.interactable = null;
    }

    render() {
        return (
            <div
                ref={el => this.el = el}
                className={this.props.className}
                style={Object.assign({}, this.state.style, this.props.style)}
                data-x={this.state.dataX}
                data-y={this.state.dataY}
            >
                {this.props.children}
            </div>
        );
    }
}