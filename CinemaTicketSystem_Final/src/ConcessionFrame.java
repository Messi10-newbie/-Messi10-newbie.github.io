import javax.swing.*;
import java.awt.*;
import java.util.ArrayList;

public class ConcessionFrame extends JFrame {

    private static final long serialVersionUID = 1L;

    public ConcessionFrame(String movieTitle, double ticketTotal,
                           ArrayList<String[]> sessionBookings, String username) {
        ConcessionOrder order = new ConcessionOrder(username);

        setTitle("Concession Stand");
        setSize(500, 500);
        setLayout(new BorderLayout(10, 10));

        JPanel menuPanel = new JPanel(new GridLayout(0, 1, 5, 5));
        menuPanel.setBorder(BorderFactory.createTitledBorder("Select Items"));

        DefaultListModel<String> orderModel = new DefaultListModel<>();
        JList<String> orderList = new JList<>(orderModel);
        JLabel totalLabel = new JLabel("Concession Total: RM0.00");

        // Popcorn buttons — depend only on ConcessionItemFactory, not on Popcorn directly
        String[] popcornSizes = {"Small", "Medium", "Large"};
        for (String size : popcornSizes) {
            ConcessionItem sample = ConcessionItemFactory.createPopcorn(size);
            JButton btn = new JButton("[Popcorn] " + size + " - RM" + sample.getPrice());
            btn.addActionListener(e -> {
                order.addItem(ConcessionItemFactory.createPopcorn(size));
                orderModel.addElement(size + " Popcorn - RM" + sample.getPrice());
                totalLabel.setText("Concession Total: RM" + String.format("%.2f", order.getTotalCost()));
            });
            menuPanel.add(btn);
        }

        // Drink buttons
        String[] drinks = {"Water", "Coke", "Pepsi", "Juice"};
        for (String drink : drinks) {
            ConcessionItem sample = ConcessionItemFactory.createDrink(drink);
            JButton btn = new JButton("[Drink] " + drink + " - RM" + sample.getPrice());
            btn.addActionListener(e -> {
                order.addItem(ConcessionItemFactory.createDrink(drink));
                orderModel.addElement(drink + " - RM" + sample.getPrice());
                totalLabel.setText("Concession Total: RM" + String.format("%.2f", order.getTotalCost()));
            });
            menuPanel.add(btn);
        }

        // Combo buttons
        String[] combos = {"Standard Combo", "Couple Combo", "Family Combo"};
        for (String combo : combos) {
            ConcessionItem sample = ConcessionItemFactory.createCombo(combo);
            JButton btn = new JButton("[Combo] " + combo + " - RM" + sample.getPrice());
            btn.addActionListener(e -> {
                order.addItem(ConcessionItemFactory.createCombo(combo));
                orderModel.addElement(combo + " - RM" + sample.getPrice());
                totalLabel.setText("Concession Total: RM" + String.format("%.2f", order.getTotalCost()));
            });
            menuPanel.add(btn);
        }

        JButton deleteItemBtn = new JButton("Delete Selected");
        deleteItemBtn.addActionListener(e -> {
            int index = orderList.getSelectedIndex();
            if (index == -1) {
                JOptionPane.showMessageDialog(this, "Please select an item to delete.");
                return;
            }
            order.getItems().remove(index);
            orderModel.remove(index);
            totalLabel.setText("Concession Total: RM" + String.format("%.2f", order.getTotalCost()));
        });

        JButton deleteAllBtn = new JButton("Delete All");
        deleteAllBtn.addActionListener(e -> {
            if (order.isEmpty()) {
                JOptionPane.showMessageDialog(this, "Your order is already empty!");
                return;
            }
            order.getItems().clear();
            orderModel.clear();
            totalLabel.setText("Concession Total: RM0.00");
        });

        JPanel orderBtnPanel = new JPanel(new FlowLayout());
        orderBtnPanel.add(deleteItemBtn);
        orderBtnPanel.add(deleteAllBtn);

        JPanel orderPanel = new JPanel(new BorderLayout());
        orderPanel.setBorder(BorderFactory.createTitledBorder("Your Order"));
        orderPanel.add(new JScrollPane(orderList), BorderLayout.CENTER);
        orderPanel.add(orderBtnPanel, BorderLayout.NORTH);
        orderPanel.add(totalLabel, BorderLayout.SOUTH);

        JButton confirmBtn = new JButton("Confirm & Pay");
        confirmBtn.addActionListener(e -> {
            String[] paymentMethods = {"Credit/Debit Card", "Touch 'n Go eWallet", "Online Banking", "Cash"};
            String selectedPayment = (String) JOptionPane.showInputDialog(
                this, "Select Payment Method:", "Payment",
                JOptionPane.QUESTION_MESSAGE, null, paymentMethods, paymentMethods[0]
            );
            if (selectedPayment == null) return;

            if (selectedPayment.equals("Credit/Debit Card")) {
                String cvv = JOptionPane.showInputDialog(this,
                    "Enter last 4 digits of card (cvv):", "Card Details", JOptionPane.PLAIN_MESSAGE);
                if (cvv == null || !cvv.matches("\\d{4}")) {
                    JOptionPane.showMessageDialog(this, "Invalid! Must be 4 digits.", "Error", JOptionPane.ERROR_MESSAGE);
                    return;
                }
            }

            dispose();
            ReceiptFrame.show(movieTitle, ticketTotal, sessionBookings, order.getItems(), selectedPayment, username);
        });

        add(new JScrollPane(menuPanel), BorderLayout.WEST);
        add(orderPanel, BorderLayout.CENTER);
        add(confirmBtn, BorderLayout.SOUTH);
        setLocationRelativeTo(null);
    }
}
