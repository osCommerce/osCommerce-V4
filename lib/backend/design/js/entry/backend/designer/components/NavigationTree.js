import React from 'react';
import globals from 'src/globals';

export default class NavigationTree extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            width: 200,
            openedItems: [],
            closedItems: []
        };

        this.toggle = this.toggle.bind(this);
    }

    searchMenuLevel (array){
        let foundItems = false;
        const changedArray = array.map(menuItem => {
            let foundSubItems = false,
                child,
                title,
                hided;

            if (menuItem.child) {
                [child, foundSubItems] = this.searchMenuLevel(menuItem.child)
            }

            let foundInCurrentItem = this.props.search && menuItem.title.toLowerCase().includes(this.props.search.toLowerCase());
            if (foundInCurrentItem || foundSubItems) {
                foundItems = true;
                hided = false
            } else {
                hided = true
            }

            if (foundInCurrentItem) {
                let re = new RegExp('(' + this.props.search + ')', 'i');
                title = menuItem.title.replace(re, '<span class="selected">$1</span>')
            } else {
                title = menuItem.title
            }

            if (!this.props.search) {
                hided = false;
            }

            return {
                hided: hided,
                showSubLevel: !this.props.search || foundInCurrentItem,
                path: menuItem.path,
                acl: menuItem.acl,
                filename: menuItem.filename,
                title: title,
                child: child,
                opened: foundSubItems && !foundInCurrentItem,

                acl_check: menuItem.acl_check,
                box_id: menuItem.box_id,
                box_type: menuItem.box_type,
                config_check: menuItem.config_check,
                dis_module: menuItem.dis_module,
                parent_id: menuItem.parent_id,
                path: menuItem.path,
                sort_order: menuItem.sort_order,
            }
        });
        return [changedArray, foundItems]
    }

    toggle(key){
        if (this.state.openedItems.includes(key)) {
            const openedItems = this.state.openedItems.filter(item => item !== key );
            this.setState({openedItems: openedItems});

            const closedItems = [...this.state.closedItems];
            closedItems.push(key);
            this.setState({closedItems: closedItems})
        } else {
            const openedItems = [...this.state.openedItems];
            openedItems.push(key);
            this.setState({openedItems: openedItems});

            const closedItems = this.state.closedItems.filter(item => item !== key );
            this.setState({closedItems: closedItems})
        }
    }

    navigationItem (data, obj) {
        const key = typeof data.box_id !== 'undefined' ? data.box_id.toString() : '0';
        let open = obj.state.openedItems.includes(key);

        if (obj.props.treeData.selectedMenu[0] === data.acl && !obj.state.closedItems.includes(key)) {
            open = true
        }
        if (data.opened) {
            open = true
        }

        if (data.child && data.child.length) {
            return (
                <li key={key} className={'item' + (open ? ' open' : '') + (obj.props.treeData.selectedMenu[0] === data.acl ? ' current' : '')} style={{display: data.hided ? 'none' : 'block'}}>
                    <a className="folder" onClick={() => this.toggle(key)}>
                        <i className={'icon-' + data.filename}></i> <span dangerouslySetInnerHTML={{__html: data.title}} />
                        <i className={'arrow' + (open ? ' icon-minus' : ' icon-plus')}></i>
                    </a>
                    <ul>{data.child.map(item => this.navigationItem(item, this))}</ul>
                </li>)
        } else {
            return (
                <li key={key} className={'item' + (obj.props.treeData.selectedMenu[0] === data.acl ? ' current' : '')} style={{display: data.hided ? 'none' : 'block'}}>
                    <a href={globals(['mainUrl']) + '/' + data.path}>
                        <i className={'icon-' + data.filename}></i> <span dangerouslySetInnerHTML={{__html: data.title}} />
                    </a>
                </li>)
        }
    }

    menuByType (menu) {
        let newMenu = JSON.parse(JSON.stringify(menu))
        if (this.props.type === 'basic') {
            newMenu = newMenu.filter((e, i) => i < 4)
        }
        return newMenu
    }

    render() {
        const menuByType = this.menuByType(this.props.treeData.menu)
        const [menu, foundItems] = this.searchMenuLevel(menuByType);

        return (
            <ul className="navigation-tree">
                {menu.map(item => this.navigationItem(item, this))}
            </ul>
        );
    }
}